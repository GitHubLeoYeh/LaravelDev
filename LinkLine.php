<?php
require_once('../include/gmtool.php');
// 功能選擇	
switch( $actionStr ) {		
		case "add_connection" :
			add_connection($_POST['newconnection']);
			break;
		case "delete_connection" :
			delete_connection($_POST['connection_id']);
			break;		
		case "update_connection" :
			update_connection($_POST['update']);
			break;													
}

// 頁面選擇
switch( $func_pageStr ) {
		case "main" :
			connection_page();
			break;			
}
/**************************************************************
*														功能															*
***************************************************************/

// 新增功能
function add_connection($addInfo)
{
	global $gmtool_db;
	
	$sql = "INSERT INTO platform_connection( connection_name, connection_ip, connection_note, connection_group, update_time) 
	VALUES ('".$addInfo['name']."', '".$addInfo['ip']."', '".$addInfo['note']."', '".$addInfo['group']."', now());";

	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));	
	pg_free_result ($acc);	
	
	//function_page();
}
// 刪除功能
function delete_connection($connection_id)
{
	global $gmtool_db;
	
	// 刪除功能
	$sql = "DELETE FROM platform_connection WHERE connection_id='".$connection_id."';";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
	pg_free_result ($acc);
	
	//function_page();
}

// 更新功能
function update_connection($updateInfo)
{
	global $gmtool_db;
	
	$sql = "UPDATE platform_connection SET \"connection_name\"='".$updateInfo['name']."',
	 connection_ip='".$updateInfo['ip']."', connection_note='".$updateInfo['note']."', connection_group='".$updateInfo['group']."', update_time='now()' WHERE connection_id='".$updateInfo['id']."';";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
	pg_free_result ($acc);	

	//function_page();
}

/**************************************************************
*														頁面															*
***************************************************************/


// 連線管理設定
function connection_page()
{
	global $lang_w;
	global $gmtool_db;

	$result  = '';
	$result .= '<!DOCTYPE HTML>';
	$result .= '<html>';
	$result .= '<title>PlatformManagement</title>';
	$result .= '<head>';
	$result .= '<meta charset="utf-8" />';			
	$result .= '<link rel="stylesheet" href="../css/basic.css" />';
	$result .= '</head>';	
	$result .= '<body class="gbody">';
	$result .= '	<header>';
	$result .= $lang_w['platform_connection_manage'];
	$result .= '	</header>';
	$result .= '	<hr>';
	$result .= '	<fieldset>';
	$result .= '	<legend>'.$lang_w['connection_setting'].'</legend>';
	$result .= '	<div class="light_table">';	
	$result .= '<pre><font color=red size=2>'.$lang_w['platform_connection_manage_note'].'<br>';
	$result .= '1) '.$lang_w['platformconnection_tip1'].'<br>';
	$result .= '2) '.$lang_w['platformconnection_tip2'].'<br>';
	$result .= 'ex. '.$lang_w['platformconnection_ex1'].'<br>';
	$result .= 'ps. '.$lang_w['platformconnection_ps1'].'<br>';
		
	$result .= '		<form name=formAddConnection method=POST action="PlatformConnection.php?func_page=main">';
	// 新增功能
	$result .= '		<table>';
	$result .= '				<tr align=center><th colspan=5>'.$lang_w['new_connection'].'</th></tr>';
	$result .= '				<tr align=center><th>'.$lang_w['connection_name'].'</th><th>'.$lang_w['connection_ip'].'</th><th>'.$lang_w['explain'].'</th><th>'.$lang_w['group'].'</th><th>'.$lang_w['action'].'</th></tr>';
	$result .= '				<td><input type=text size=16 name=newconnection[name] ></td>';
	$result .= '				<td><input type=text size=16 name=newconnection[ip] ></td>';
	$result .= '				<td><input type=text size=25 name=newconnection[note] ></td>';	
	$result .= '				<td><input type=text size=16 name=newconnection[group]></td>';
	$result .= '				<td><input type=submit size=1 name=btnAddConnection value="'.$lang_w['add'].'"></td>';
	$result .= '		</table>';	
	$result .= '			<input type=hidden name=actions value=add_connection>';	
	$result .= '		</form>';	
	$result .= '		<br>';	

	// 顯示與修改功能
	$result .= ' 		<table>';
	$result .= '				<tr align=center><th>'.$lang_w['connection_name'].'</th><th>'.$lang_w['connection_ip'].'</th><th>'.$lang_w['explain'].'</th><th>'.$lang_w['group'].'</th><th>'.$lang_w['last_update'].'</th><th colspan=2>'.$lang_w['action'].'</th></tr>';
	
		$sql = "SELECT *, to_char(update_time, 'YYYY/MM/DD HH24:MI:SS') as update_time FROM platform_connection order by connection_id";
		$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));		

	while($row_acc = pg_fetch_assoc($acc))
	{			
		$result .= '			<tr>';
		$result .= '			<form name=formUpdateConnection method=POST action="PlatformConnection.php?func_page=main">';
		$result .= "			<input type=hidden name=update[id] size=1 ReadOnly value='".$row_acc['connection_id']."' >";
		$result .= "			<td><input type=text name=update[name] size=16 value='".$row_acc['connection_name']."' ></td>";
		$result .= "			<td><input type=text name=update[ip] size=16 value='".$row_acc['connection_ip']."' ></td>";
		$result .= "			<td><input type=text name=update[note] size=25 value='".$row_acc['connection_note']."' ></td>";
		$result .= "			<td><input type=text name=update[group] size=16 value='".$row_acc['connection_group']."' ></td>";
		$result .= "			<td>".$row_acc['update_time']."</td>";
		$result .= '			<input type=hidden name=actions value=update_connection>';	
		$result .= '			<td><input type=submit name=submitUpdateConnection value="'.$lang_w['edit'].'"></td>';				
		$result .= '			</form>';
		
		$result .= '			<form name=formDeleteConnection method=POST action="PlatformConnection.php?func_page=main">';
		$result .= '			<td><input type=submit name=btnDelete value="'.$lang_w['delete'].'" ></td>';
		$result .= '			<input type=hidden name=actions value=delete_connection>';
		$result .= "			<input type=hidden name=connection_id value='".$row_acc['connection_id']."'>";			
		$result .= '			</form>';		
		
		$result .= '			</tr>';		
	}
		pg_free_result ($acc);	
	$result .= '		</table>';
	$result .= '	</div>';
	$result .= '  </fieldset>';
	$result .= '</body>';
	$result .= '</html>';	
	echo $result;
}
