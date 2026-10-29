<?php
/** Operação persistente do Assistance; carregada somente pelo controlador autenticado. */
if (!isset($pdo, $currentUser, $requestUri)) { http_response_code(404); exit; }
if (!preg_match('#^/(dashboard|ordens|chamados|pendencias|tipos-servico|usuarios|dados-operacionais)(/|$)#', $requestUri) && $requestUri !== '/' && $requestUri !== '/assistance-ia/rascunho') return;
date_default_timezone_set('America/Sao_Paulo');
function opQuery(string $sql, array $params = []): array { global $pdo; $q = $pdo->prepare($sql); $q->execute($params); return $q->fetchAll(); }
function opFail(int $status, string $message): never { http_response_code($status); echo '<h1>' . htmlspecialchars($message) . '</h1><p><a href="/dashboard">Voltar ao painel</a></p>'; exit; }
function opView(string $view, array $data = []): never { global $currentUser, $isAdmin, $baseUrl, $csrfToken, $flash, $pendingPasswordResetCount; extract($data); require __DIR__ . '/views/' . $view . '.php'; exit; }
function opStatus(string $value): string { return strtr($value, ['Concluída'=>'Concluida','Concluído'=>'Concluido','Em diagnóstico'=>'Em diagnostico','Aguardando aprovação'=>'Aguardando aprovacao']); }
function opDisplay(string $value): string { return strtr($value, ['Concluida'=>'Concluída','Concluido'=>'Concluído','Em diagnostico'=>'Em diagnóstico','Aguardando aprovacao'=>'Aguardando aprovação']); }
function opClosed(string $status): bool { return in_array(opStatus($status), ['Concluida','Concluido','Cancelada','Cancelado'], true); }
function opDate(?string $value): string { return $value ? date('d/m/Y H:i', strtotime($value)) : '-'; }
function opRecord(string $module, int $id, bool $lock = false): array {
    $tables=['ordens'=>'ordens_servico','chamados'=>'chamados_online','pendencias'=>'pendencias','tipos-servico'=>'tipos_servico','usuarios'=>'usuarios'];
    $rows=opQuery('SELECT * FROM '.$tables[$module].' WHERE id = ?'.($lock?' FOR UPDATE':''),[$id]);
    if (!$rows) opFail(404,'Registro não encontrado.');
    return $rows[0];
}
function opAccess(string $module, array $record, bool $write = false): void {
    global $isAdmin, $currentUser;
    if ($isAdmin) return;
    if (in_array($module,['usuarios','tipos-servico'],true)) opFail(403,'Acesso restrito ao administrador.');
    $key=$module==='pendencias'?'responsavel_id':'tecnico_id';
    $owner=$record[$key]??null;
    if ((int)$owner !== (int)$currentUser['id'] && !($module==='ordens' && $owner===null)) opFail(403,'Este atendimento pertence a outro responsável.');
}
function opRows(string $module): array {
    global $isAdmin,$currentUser;
    $queries=[
      'ordens'=>"SELECT o.*, c.razao_social cliente_nome, c.cnpj cliente_cnpj, c.telefone cliente_telefone, c.email cliente_email, c.endereco cliente_endereco, t.nome servico_nome, u.nome tecnico_nome FROM ordens_servico o JOIN clientes c ON c.id=o.cliente_id JOIN tipos_servico t ON t.id=o.tipo_servico_id LEFT JOIN usuarios u ON u.id=o.tecnico_id",
      'chamados'=>"SELECT o.*, c.razao_social cliente_nome, u.nome tecnico_nome FROM chamados_online o JOIN clientes c ON c.id=o.cliente_id LEFT JOIN usuarios u ON u.id=o.tecnico_id",
      'pendencias'=>"SELECT o.*, u.nome responsavel_nome FROM pendencias o LEFT JOIN usuarios u ON u.id=o.responsavel_id"
    ];
    $sql=$queries[$module]; $params=[];
    if (!$isAdmin) { $field=$module==='pendencias'?'responsavel_id':'tecnico_id'; $sql.=' WHERE (o.'.$field.' = ?'.($module==='ordens'?' OR o.tecnico_id IS NULL':'').')'; $params[]=(int)$currentUser['id']; }
    $sql.=' ORDER BY o.id DESC';
    $rows=opQuery($sql,$params);
    foreach($rows as &$r) {
       $r['status']=opDisplay($r['status']);
       $deadline=$r['prazo_previsto']??$r['prazo']??null;
       $r['prazo_formatado']=opDate($deadline);
       $r['prazo_violado']=$deadline && strtotime($deadline)<time() && !opClosed($r['status']);
       $r['abertura_em_formatada']=opDate($r['abertura_em']??null);
       $r['aberto_em_formatada']=opDate($r['aberto_em']??null);
       $r['criado_em_formatado']=opDate($r['criado_em']??null);
       $r['conclusao_em_formatada']=opDate($r['conclusao_em']??null);
    } unset($r); return $rows;
}
function opFilter(array $rows): array {
    global $isAdmin;
    $query=mb_strtolower(trim((string)($_GET['busca']??'')));
    return array_values(array_filter($rows, function($r)use($query,$isAdmin){
      if($query!=='' && !str_contains(mb_strtolower(implode(' ',array_filter($r,'is_scalar'))),$query)) return false;
      if(!empty($_GET['status']) && opStatus($r['status'])!==opStatus((string)$_GET['status'])) return false;
      if(!empty($_GET['prioridade']) && ($r['prioridade']??'')!==$_GET['prioridade']) return false;
      if($isAdmin && !empty($_GET['tecnico_id']) && (int)($r['tecnico_id']??$r['responsavel_id']??0)!==(int)$_GET['tecnico_id']) return false;
      $deadline=$r['prazo_previsto']??$r['prazo']??null;
      $filter=(string)($_GET['prazo']??'');
      if($filter==='atrasados' && empty($r['prazo_violado'])) return false;
      if($filter==='hoje' && (!$deadline || date('Y-m-d',strtotime($deadline))!==date('Y-m-d') || opClosed($r['status']))) return false;
      if($filter==='abertos' && opClosed($r['status'])) return false;
      return true;
    }));
}
function opBackup(): void {
    global $pdo;
    $dir=__DIR__.'/storage/operational-backups';
    if(!is_dir($dir) && !@mkdir($dir,0700,true)) throw new RuntimeException('Não foi possível criar a pasta privada de backup.');
    $file=$dir.'/dados-'.date('Y-m-d').'.json';
    if(is_file($file)){ $existing=json_decode((string)file_get_contents($file),true); if(!is_array($existing)||!isset($existing['tabelas']))throw new RuntimeException('A cópia diária existente precisa de revisão antes de salvar.'); return; }
    $pdo->beginTransaction();
    try {
      $data=['versao'=>1,'gerado_em'=>date(DATE_ATOM),'tipo'=>'dados_operacionais','tabelas'=>[]];
      foreach(['clientes','tipos_servico','ordens_servico','chamados_online','pendencias'] as $table) $data['tabelas'][$table]=opQuery('SELECT * FROM '.$table);
      $data['tabelas']['usuarios']=opQuery('SELECT id,nome,email,perfil,ativo,criado_em,atualizado_em FROM usuarios');
      $json=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR);
      $pdo->commit();
      $handle=@fopen($file,'x');
      if(!$handle) { if(is_file($file)) return; throw new RuntimeException('Não foi possível gravar o backup privado.'); }
      try { if(fwrite($handle,$json)!==strlen($json)) throw new RuntimeException('Backup incompleto.'); } finally { fclose($handle); }
      @chmod($file,0600);
    } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); throw $e; }
}
function opInput(string $module, ?array $old): array {
    global $isAdmin,$currentUser,$clienteRepository;
    $errors=[]; $d=[];
    $required=match($module){'ordens'=>['equipamento_tipo'=>80,'equipamento_marca'=>80,'equipamento_modelo'=>100,'defeito'=>10000], 'chamados'=>['descricao'=>10000], 'pendencias'=>['titulo'=>160,'descricao'=>10000], 'tipos-servico'=>['nome'=>140], default=>[]};
    foreach($required as $key=>$max){$d[$key]=trim((string)($_POST[$key]??''));if($d[$key]===''||mb_strlen($d[$key])>$max)$errors[$key]='Preencha '.$key.' (até '.$max.' caracteres).';}
    $optional=match($module){'ordens'=>['diagnostico','observacoes'],'chamados'=>['solucao'],'tipos-servico'=>['descricao'],default=>[]};
    foreach($optional as $key){$d[$key]=trim((string)($_POST[$key]??''));if(mb_strlen($d[$key])>10000)$errors[$key]='O texto excedeu 10.000 caracteres.';}
    if(in_array($module,['ordens','chamados'],true)){
      $d['cliente_id']=(int)($_POST['cliente_id']??0);
      $client=$clienteRepository->find($d['cliente_id'],$currentUser);
      if(!$client || (empty($client['ativo']) && (int)($old['cliente_id']??0)!==$d['cliente_id']))$errors['cliente_id']='Selecione um cliente acessível e ativo.';
    }
    if($module==='ordens'){
      $d['tipo_servico_id']=(int)($_POST['tipo_servico_id']??0);
      if(!opQuery('SELECT id FROM tipos_servico WHERE id=? AND (ativo=1 OR id=?)',[$d['tipo_servico_id'],(int)($old['tipo_servico_id']??0)]))$errors['tipo_servico_id']='Selecione um serviço ativo.';
    }
    if($module!=='tipos-servico'){
      $ownerKey=$module==='pendencias'?'responsavel_id':'tecnico_id';
      $d[$ownerKey]=$isAdmin?((int)($_POST[$ownerKey]??0)?:null):(int)$currentUser['id'];
      if($module!=='ordens' && !$d[$ownerKey])$errors[$ownerKey]='Selecione um responsável.';
      if($d[$ownerKey] && !opQuery('SELECT id FROM usuarios WHERE id=? AND ativo=1',[$d[$ownerKey]]))$errors[$ownerKey]='Responsável inválido ou inativo.';
      if($module!=='chamados'){
        $d['prioridade']=$isAdmin?(string)($_POST['prioridade']??'media'):(string)($old['prioridade']??'media');
        if(!in_array($d['prioridade'],['baixa','media','alta'],true))$errors['prioridade']='Prioridade inválida.';
      }
      $allowed=match($module){'ordens'=>['Aberta','Em diagnostico','Aguardando aprovacao','Em andamento','Concluida','Cancelada'],'chamados'=>['Aberto','Em atendimento','Concluido','Cancelado'],default=>['Pendente','Em andamento','Concluida','Cancelada']};
      $d['status']=opStatus((string)($_POST['status']??$allowed[0]));
      if(!in_array($d['status'],$allowed,true))$errors['status']='Status inválido.';
      if($old && opClosed($old['status']) && !$isAdmin && !opClosed($d['status']))$errors['status']='Somente o administrador pode reabrir atendimentos.';
      if(in_array($d['status'],['Concluida','Concluido'],true)){
        $field=$module==='ordens'?'diagnostico':($module==='chamados'?'solucao':'descricao');
        if(trim((string)($d[$field]??''))==='')$errors[$field]='Registre o diagnóstico ou a solução antes de concluir.';
      }
      $dateFields=match($module){'ordens'=>['abertura_em','prazo_previsto'],'chamados'=>['aberto_em'],default=>['prazo']};
      foreach($dateFields as $key){
        $raw=(string)($_POST[$key]??'');
        $dt=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$raw);
        if(!$dt || $dt->format('Y-m-d\TH:i')!==$raw){$errors[$key]='Informe uma data e hora válida.';$d[$key]=$raw;}else $d[$key]=$dt->format('Y-m-d H:i:s');
      }
      if($module==='ordens' && empty($errors['abertura_em']) && empty($errors['prazo_previsto']) && $d['prazo_previsto']<=$d['abertura_em'])$errors['prazo_previsto']='O prazo deve ser posterior à abertura.';
      $closedKey=$module==='ordens'?'conclusao_em':'concluido_em';
      $d[$closedKey]=opClosed($d['status'])?($old[$closedKey]??date('Y-m-d H:i:s')):null;
    }else $d['ativo']=((string)($_POST['ativo']??'')==='1')?1:0;
    return [$d,$errors];
}
$tecnicos=opQuery('SELECT id,nome,email,perfil,ativo FROM usuarios WHERE ativo=1 ORDER BY nome');
if($requestUri==='/assistance-ia/rascunho'){
    if($_SERVER['REQUEST_METHOD']!=='POST')opFail(405,'Use o botão de gerar rascunho.');
    verifyCsrf(); $conversation=(array)($_SESSION['ethan_ai_chat']??[]); $problem=[];$suggestions=[];
    foreach($conversation as $msg){if(($msg['role']??'')==='user')$problem[]=(string)$msg['content'];else $suggestions[]=(string)($msg['content']??'');}
    if(!$problem)redirectWithFlash('/assistance-ia','danger','Converse com o assistente antes de preparar um rascunho.');
    $draftType=opQuery("SELECT id FROM tipos_servico WHERE ativo=1 AND nome LIKE 'Diagn%' ORDER BY id LIMIT 1");
    $_SESSION['operational_draft']=['tipo_servico_id'=>(int)($draftType[0]['id']??0),'defeito'=>mb_substr(implode("\n\n",$problem),0,10000),'observacoes'=>mb_substr('Sugestões da IA para revisão pelo técnico (não confirmadas): '.implode("\n\n",$suggestions),0,10000)];
    header('Location: /ordens/nova?rascunho=ia'); exit;
}
if($requestUri==='/dados-operacionais/backup'){
    requireAdmin($isAdmin);if($_SERVER['REQUEST_METHOD']!=='POST')opFail(405,'Use o formulário de backup.');verifyCsrf();
    opBackup(); $securityService->audit((int)$currentUser['id'],'exportar_backup','operacao');
    header('Content-Type: application/json; charset=utf-8');header('Content-Disposition: attachment; filename="assistance-dados-'.date('Y-m-d').'.json"');header('Cache-Control: no-store');readfile(__DIR__.'/storage/operational-backups/dados-'.date('Y-m-d').'.json');exit;
}
if($requestUri==='/' || $requestUri==='/dashboard'){
    $tecnicoFiltroId=$isAdmin?(string)($_GET['tecnico_id']??''):(string)$currentUser['id'];
    $sets=['ordens'=>opRows('ordens'),'chamados'=>opRows('chamados'),'pendencias'=>opRows('pendencias')];
    if($tecnicoFiltroId!=='')foreach($sets as &$rows)$rows=array_values(array_filter($rows,fn($r)=>(int)($r['tecnico_id']??$r['responsavel_id']??0)===(int)$tecnicoFiltroId)); unset($rows);
    $metricas=['servicos_concluidos'=>0,'chamados_resolvidos'=>0,'pendencias_concluidas'=>0,'tarefas_atrasadas'=>0,'tempo_medio'=>'—','conclusao_no_prazo'=>'—'];$atividadesRecentes=[];$durations=[];$completedWithDeadline=0;$onTime=0;
    foreach($sets as $mod=>$rows)foreach($rows as $r){
      $status=opStatus($r['status']);$done=in_array($status,['Concluida','Concluido'],true);
      if($done){$metricas[match($mod){'ordens'=>'servicos_concluidos','chamados'=>'chamados_resolvidos',default=>'pendencias_concluidas'}]++;$start=$r['abertura_em']??$r['aberto_em']??$r['criado_em'];$end=$r['conclusao_em']??$r['concluido_em']??null;if($end&&strtotime($end)>=strtotime($start))$durations[]=strtotime($end)-strtotime($start);$deadline=$r['prazo_previsto']??$r['prazo']??null;if($deadline&&$end){$completedWithDeadline++;if($end<=$deadline)$onTime++;}}
      if(!empty($r['prazo_violado']))$metricas['tarefas_atrasadas']++;
      if(!opClosed($r['status']))$atividadesRecentes[]=['id'=>$r['id'],'tipo_registro'=>match($mod){'ordens'=>'Ordem de Serviço','chamados'=>'Chamado Online',default=>'Pendência Interna'},'titulo_ou_cliente'=>$r['titulo']??$r['cliente_nome'],'tecnico_nome'=>$r['tecnico_nome']??$r['responsavel_nome']??'Não atribuído','prioridade'=>$r['prioridade']??'media','status'=>$r['status'],'prazo_formatado'=>$r['prazo_formatado'],'prazo_violado'=>$r['prazo_violado'],'url_modulo'=>$mod,'sort'=>$r['prazo_previsto']??$r['prazo']??'9999'];
    }
    if(($_GET['prazo']??'')==='atrasados')$atividadesRecentes=array_values(array_filter($atividadesRecentes,fn($a)=>!empty($a['prazo_violado'])));
    usort($atividadesRecentes,fn($a,$b)=>strcmp($a['sort'],$b['sort']));$atividadesRecentes=array_slice($atividadesRecentes,0,20);
    if($durations){$minutes=(int)round(array_sum($durations)/count($durations)/60);$metricas['tempo_medio']=intdiv($minutes,60).'h '.($minutes%60).'min';}
    if($completedWithDeadline)$metricas['conclusao_no_prazo']=number_format(100*$onTime/$completedWithDeadline,1,',','.').'%';

    // Série diária usada no indicador visual da dashboard. O gráfico representa
    // chamados efetivamente resolvidos (e não chamados recebidos), por isso uma
    // tendência de alta é uma melhora operacional.
    $graficoChamados=['labels'=>[],'valores'=>[],'tendencia'=>'estavel','variacao'=>0];
    $inicioGrafico=(new DateTimeImmutable('today'))->modify('-6 days');
    for($dia=0;$dia<7;$dia++){
      $data=$inicioGrafico->modify('+'.$dia.' days');
      $chave=$data->format('Y-m-d');
      $graficoChamados['labels'][]=$data->format('d/m');
      $graficoChamados['valores'][$chave]=0;
    }
    foreach($sets['chamados'] as $chamado){
      if(!in_array(opStatus((string)($chamado['status']??'')),['Concluido','Concluida'],true))continue;
      $concluidoEm=(string)($chamado['concluido_em']??'');
      if($concluidoEm==='')continue;
      $chave=date('Y-m-d',strtotime($concluidoEm));
      if(array_key_exists($chave,$graficoChamados['valores']))$graficoChamados['valores'][$chave]++;
    }
    $graficoChamados['valores']=array_values($graficoChamados['valores']);
    $primeiro=(int)$graficoChamados['valores'][0];
    $ultimo=(int)$graficoChamados['valores'][6];
    $graficoChamados['variacao']=$ultimo-$primeiro;
    $graficoChamados['tendencia']=$ultimo>$primeiro?'alta':($ultimo<$primeiro?'queda':'estavel');

    opView('dashboard/index',compact('metricas','tecnicos','atividadesRecentes','tecnicoFiltroId','graficoChamados'));
}
if(preg_match('#^/(ordens|chamados|pendencias|tipos-servico)(?:/(nova|novo|salvar|\d+)(?:/(editar|atualizar|assumir|reabrir|atualizar-status|concluir|imprimir))?)?$#',$requestUri,$match)){
    $module=$match[1];$segment=$match[2]??'';$action=$match[3]??'';$id=ctype_digit($segment)?(int)$segment:null;
    $folder=$module==='tipos-servico'?'tipos_servico':$module;
    $entity=match($module){'ordens'=>'ordem_servico','chamados'=>'chamado_online','pendencias'=>'pendencia',default=>'tipo_servico'};
    if($module==='tipos-servico')requireAdmin($isAdmin);
    $old=$id?opRecord($module,$id):null;if($old)opAccess($module,$old);
    $tables=['ordens'=>'ordens_servico','chamados'=>'chamados_online','pendencias'=>'pendencias','tipos-servico'=>'tipos_servico'];
    $clients=$clienteRepository->all($currentUser);$types=opQuery('SELECT * FROM tipos_servico ORDER BY nome');
    $data=['clientes'=>$clients,'tiposServico'=>$types,'tecnicos'=>$tecnicos,'usuarios'=>$tecnicos,'errors'=>[]];
    $var=match($module){'ordens'=>'ordem','chamados'=>'chamado','pendencias'=>'pendencia',default=>'tipoServico'};
    if($_SERVER['REQUEST_METHOD']==='POST'){
      verifyCsrf();
      try {
        opBackup();$pdo->beginTransaction();
        if($id){$old=opRecord($module,$id,true);opAccess($module,$old,true);}
        if($id && in_array($action,['assumir','reabrir','atualizar-status','concluir'],true)){
          if($action==='assumir'){
            if($module!=='ordens'||$old['tecnico_id']!==null)throw new InvalidArgumentException('Esta OS já tem responsável.');
            if(opClosed($old['status']))throw new InvalidArgumentException('Uma OS encerrada não pode ser assumida.');
            $fields=['tecnico_id'=>(int)$currentUser['id']];
          }elseif($action==='reabrir'){
            requireAdmin($isAdmin);if($module!=='ordens')throw new InvalidArgumentException('Ação inválida.');$fields=['status'=>'Aberta','conclusao_em'=>null];
          }else{
            $candidate=$old;$candidate['status']=$action==='concluir'?'Concluida':opStatus((string)($_POST['status']??''));
            $savedPost=$_POST;$_POST=array_merge($old,['status'=>$candidate['status']]);
            foreach(['abertura_em','prazo_previsto','aberto_em','prazo'] as $key)if(!empty($_POST[$key]))$_POST[$key]=date('Y-m-d\TH:i',strtotime($_POST[$key]));
            [$validated,$errors]=opInput($module,$old);$_POST=$savedPost;if($errors)throw new InvalidArgumentException(implode(' ',$errors));
            $fields=['status'=>$validated['status']];$closeKey=$module==='ordens'?'conclusao_em':'concluido_em';$fields[$closeKey]=$validated[$closeKey];
          }
        }else{
          if(!(($segment==='salvar'&&!$id)||($id&&$action==='atualizar')))throw new InvalidArgumentException('Ação de gravação inválida.');
          [$fields,$errors]=opInput($module,$old);
          if($errors){$pdo->rollBack();$record=array_merge($old??[],$fields);if(isset($record['status']))$record['status']=opDisplay($record['status']);$data[$var]=$record;$data['errors']=$errors;opView($folder.'/form',$data);}
        }
        if($id){$parts=[];foreach(array_keys($fields)as$key)$parts[]=$key.'=?';$stmt=$pdo->prepare('UPDATE '.$tables[$module].' SET '.implode(',',$parts).' WHERE id=?');$stmt->execute([...array_values($fields),$id]);}
        else{$stmt=$pdo->prepare('INSERT INTO '.$tables[$module].' ('.implode(',',array_keys($fields)).') VALUES ('.implode(',',array_fill(0,count($fields),'?')).')');$stmt->execute(array_values($fields));$id=(int)$pdo->lastInsertId();}
        $securityService->audit((int)$currentUser['id'],$old?'atualizar':'criar',$entity,$id,['status_anterior'=>$old['status']??null,'status'=>$fields['status']??$old['status']??null]);
        $pdo->commit();unset($_SESSION['operational_draft']);
        redirectWithFlash('/'.$module.($module==='tipos-servico'?'':'/'.$id),'success','Registro salvo com sucesso.');
      }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Operação '.$module.': '.$e->getMessage());$message=$e instanceof InvalidArgumentException?$e->getMessage():'Não foi possível salvar. Os dados existentes foram preservados. Verifique campos e vínculos e tente novamente.';redirectWithFlash('/'.$module.($id?'/'.$id.'/editar':($module==='ordens'?'/nova':'/novo')),'danger',$message);}
    }
    if($_SERVER['REQUEST_METHOD']!=='GET')opFail(405,'Método não permitido.');
    if($segment===''){
      if($module==='tipos-servico')$data['tiposServico']=$types;
      else $data[match($module){'ordens'=>'ordens','chamados'=>'chamados',default=>'pendencias'}]=opFilter(opRows($module));
      opView($folder.'/index',$data);
    }
    if($segment==='nova'||$segment==='novo'){
      $data[$var]=null;
      if($module==='ordens' && ($_GET['rascunho']??'')==='ia')$data[$var]=(array)($_SESSION['operational_draft']??[]);
      opView($folder.'/form',$data);
    }
    if(!$id)opFail(404,'Página não encontrada.');
    if($module==='tipos-servico')$record=$old;
    else{$record=null;foreach(opRows($module)as$r)if((int)$r['id']===$id){$record=$r;break;}if(!$record)opFail(404,'Registro não encontrado.');}
    $data[$var]=$record;
    $data['historicoAtendimento']=opQuery('SELECT a.acao,a.detalhes_json,a.criado_em,u.nome usuario_nome FROM audit_logs a LEFT JOIN usuarios u ON u.id=a.usuario_id WHERE a.entidade=? AND a.registro_id=? ORDER BY a.id DESC LIMIT 100',[$entity,$id]);
    if($action==='imprimir'&&$module==='ordens')opView('ordens/print',$data);
    opView($folder.'/'.(($action==='editar'||$module==='chamados'||$module==='tipos-servico')?'form':'show'),$data);
}
if($requestUri==='/usuarios') {requireAdmin($isAdmin);$usuarios=opQuery('SELECT id,nome,email,perfil,ativo,atualizado_em FROM usuarios ORDER BY nome');foreach($usuarios as &$userRow)$userRow['atualizado_em_formatado']=opDate($userRow['atualizado_em']??null);unset($userRow);opView('usuarios/index',compact('usuarios'));}
if(preg_match('#^/usuarios/(novo|salvar|\d+)(?:/(editar|atualizar))?$#',$requestUri,$um)) {
    requireAdmin($isAdmin);$uid=ctype_digit($um[1])?(int)$um[1]:null;$action=$um[2]??'';
    $usuario=$uid?opRecord('usuarios',$uid):null;if($usuario)unset($usuario['senha_hash']);$errors=[];
    if($_SERVER['REQUEST_METHOD']==='POST'){
      verifyCsrf();$data=['nome'=>trim((string)($_POST['nome']??'')),'email'=>strtolower(trim((string)($_POST['email']??''))),'perfil'=>(string)($_POST['perfil']??''),'ativo'=>((string)($_POST['ativo']??'')==='1')?1:0];$password=(string)($_POST['senha']??'');
      if($data['nome']===''||mb_strlen($data['nome'])>120)$errors[]='Informe um nome de até 120 caracteres.';
      if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL)||strlen($data['email'])>190)$errors[]='Informe um e-mail válido.';
      if(!in_array($data['perfil'],['admin','tecnico'],true))$errors[]='Perfil inválido.';
      if((!$uid||$password!=='')&&($policy= passwordPolicyError($password)))$errors[]=$policy;
      if(strlen($password)>72)$errors[]='A senha deve ter até 72 bytes.';
      if($uid===(int)$currentUser['id']&&(!$data['ativo']||$data['perfil']!=='admin'))$errors[]='Você não pode desativar sua conta nem remover seu próprio perfil de administrador.';
      if(!(($um[1]==='salvar'&&!$uid)||($uid&&$action==='atualizar')))$errors[]='Ação inválida.';
      if(!$errors){$duplicateSql='SELECT id FROM usuarios WHERE email=?'.($uid?' AND id<>?':'').' LIMIT 1';$duplicateArgs=[$data['email']];if($uid)$duplicateArgs[]=$uid;$duplicate=$pdo->prepare($duplicateSql);$duplicate->execute($duplicateArgs);if($duplicate->fetchColumn())$errors[]='Já existe um usuário cadastrado com este e-mail.';}
      if(!$errors)try{
        opBackup();$pdo->beginTransaction();$admins=opQuery("SELECT id FROM usuarios WHERE ativo=1 AND perfil='admin' ORDER BY id FOR UPDATE");
        if(!in_array((int)$currentUser['id'],array_map('intval',array_column($admins,'id')),true))throw new InvalidArgumentException('Administrador inativo. Entre novamente.');
        if($uid)$old=opRecord('usuarios',$uid,true);
        if($password!=='')$data['senha_hash']=password_hash($password,PASSWORD_DEFAULT);
        if($uid){$parts=[];foreach(array_keys($data)as$key)$parts[]=$key.'=?';$q=$pdo->prepare('UPDATE usuarios SET '.implode(',',$parts).' WHERE id=?');$q->execute([...array_values($data),$uid]);}
        else{$q=$pdo->prepare('INSERT INTO usuarios ('.implode(',',array_keys($data)).') VALUES ('.implode(',',array_fill(0,count($data),'?')).')');$q->execute(array_values($data));$uid=(int)$pdo->lastInsertId();}
        $securityService->audit((int)$currentUser['id'],'salvar','usuario',$uid,['perfil'=>$data['perfil'],'ativo'=>$data['ativo']]);$pdo->commit();redirectWithFlash('/usuarios','success','Usuário salvo com sucesso.');
      }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Usuário: '.$e->getMessage());$duplicateEmail=$e instanceof PDOException&&(string)$e->getCode()==='23000';$errors[]=$duplicateEmail?'Já existe um usuário cadastrado com este e-mail.':($e instanceof InvalidArgumentException?$e->getMessage():'Não foi possível salvar. Tente novamente.');}
      $usuario=array_merge($usuario??[],$data);unset($usuario['senha_hash']);if($uid)$usuario['id']=$uid;
    }elseif($_SERVER['REQUEST_METHOD']!=='GET')opFail(405,'Método não permitido.');
    if(!$uid&&$um[1]!=='novo'&&$_SERVER['REQUEST_METHOD']==='GET')opFail(404,'Página não encontrada.');
    opView('usuarios/form',compact('usuario','errors'));
}
