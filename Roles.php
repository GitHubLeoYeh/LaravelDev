<?php
require_once('../include/gmtool.php');
// 功能選擇	
switch( $actionStr ) {		

		case "add_group" :
			add_group($_POST['add_group']);
			break;
		case "del_group" :
			del_group($_POST['group_id']);
			break;		
		case "update_group" :
			update_group($_POST['update_group']);
			break;
		case "add_member" :
			add_member($_POST['group_id'],$_POST['nonmember']);
			break;
		case "remove_member" :
			remove_member($_POST['group_id'], $_POST['member']);
			break;
		case "update_access" :
			update_access($_POST['group_id'], $_POST['chkBox']);
			break;											
}

// 頁面選擇
switch( $func_pageStr ) {
		case "main" :
			group_page();
			break;
		case "edit_member" :
			edit_member_page($_POST['group_id']);
			break;
		case "edit_access" :
			edit_access_page($_POST['group_id']);
			break;
}
/**************************************************************
*														功能															*
***************************************************************/
function add_group ($addInfo)
{
	global $gmtool_db;

	$sql = "INSERT INTO platform_group( group_name, note) 
	VALUES ('".$addInfo['name']."', '".$addInfo['note']."');";

	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));	
	pg_free_result ($acc);
	
	//echo "新增群組完畢<br><A HREF=".basename($_SERVER["PHP_SELF"])."?actions=group>返回</A>";
	//group_page();
}
function update_group ($updateInfo)
{
	global $gmtool_db;
	
	$sql = "UPDATE platform_group SET group_name ='".$updateInfo['name']."', note='".$updateInfo['note']."' WHERE group_id='".$updateInfo['id']."';";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
	pg_free_result ($acc);
	
	//echo "修改群組完畢<br><A HREF=".basename($_SERVER["PHP_SELF"])."?actions=group>返回</A>";
	//group_page();
}

function del_group ($group_id)
{
	global $gmtool_db;
	
	$sql = "DELETE FROM platform_group WHERE group_id='".$group_id."';";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
	pg_free_result ($acc);
	
	//echo "刪除群組完畢<br><A HREF=".basename($_SERVER["PHP_SELF"])."?actions=group>返回</A>";
	//group_page();
}



// 新增組成員
function add_member($group_id, $non_member)
{
	global $gmtool_db;
	$group_integer = pow(2,($group_id-1));
	if(!$non_member) return;
	foreach($non_member as $toAdd)
	{	
		
		$sql = "SELECT group_id FROM platform_account WHERE account='".$toAdd."';";
		$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
		$row_acc = pg_fetch_assoc($acc);		
		pg_free_result ($acc);
		$new_integer = $row_acc['group_id'] + $group_integer;
	 	
		$sql = "UPDATE platform_account SET group_id='".$new_integer."' WHERE account='".$toAdd."';";
		$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
		pg_free_result ($acc);
	}
	//edit_member_page($group_id);
}

// 移除群組成員
function remove_member($group_id, $member)
{
	global $gmtool_db;
	$group_integer = pow(2,($group_id-1));
	if(!$member) return;
	foreach($member as $toRemove)
	{	
		$sql = "SELECT group_id FROM platform_account WHERE account='".$toRemove."';";
		$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
		$row_acc = pg_fetch_assoc($acc);		
		pg_free_result ($acc);
		$new_integer = $row_acc['group_id'] - $group_integer;
	 	
		$sql = "UPDATE platform_account SET group_id='".$new_integer."' WHERE account='".$toRemove."';";
		$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
		pg_free_result ($acc);
	}
	//edit_member_page($group_id);
}

// 更新權限
function update_access($group_id, $chkBox)
{
	global $gmtool_db;	
	$sql = "DELETE FROM platform_access WHERE group_id='".$group_id."';";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
	pg_free_result ($acc);
	if($chkBox)
	{ 
		foreach($chkBox as $function_id => $toUpdate)
		{
			$sql1 = "INSERT INTO platform_access( group_id, function_id) 
			VALUES ('".$group_id."', '".$function_id."');";	
			$acc1 = pg_query( $gmtool_db, $sql1) or die(pg_last_error($gmtool_db));			
		}
		pg_free_result ($acc1);
	}
	//edit_access_page($group_id);

}

/**************************************************************
*														頁面															*
***************************************************************/

// 新增移除修改群組介面
function group_page()
{
	global $lang_w,$lang_s;
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
	$result .= $lang_w['platform_group_manage'];
	$result .= '	</header>';
	$result .= '	<hr>';
	$result .= '	<fieldset>';
	$result .= '	<legend>'.$lang_w['platform_group'].'</legend>';
	$result .= '	<div class="light_table">';
	$result .= '<pre><font color=red size=2>'.$lang_w['platform_group_manage_note'].'<br>';
	$result .= '1) '.$lang_w['platformgroup_tip1'].'<br>';
	$result .= '2) '.$lang_w['platformgroup_tip2'].'<br>';
	$result .= '	<table border=1>';
	//$result .= '		<tr align=center><td>新增群組</td></tr>';
	//$result .= '		<tr><td>';
	//$result .= '				<table border=1>';
	//$result .= '					<tr align=center><td>群組名稱</td><td>註記</td><td>動作</td></tr>';
	//$result .= '					<form name=formGroup method=POST action="PlatformGroup.php?func_page=main">';
	//$result .= '					<input type=hidden name=actions value=add_group>';
	//$result .= '					<td><input type=text name=add_group[name]></td>';
	//$result .= '					<td><input type=text size=35 name=add_group[note]></td>';
	//$result .= '					<td><input type=submit value=\'新增\'></td></tr>';
	//$result .= '					</form>';
	//$result .= '				</table>';
	//$result .= '		<td></tr>';		
	$result .= '		<tr align=center><th>'.$lang_w['edit'].'</th></tr>';
	$result .= '		<tr><td>';
	$result .= '				<table >';
	$result .= '					<tr align=center><th>ID</th><th>'.$lang_w['group_name'].'</th><th>'.$lang_w['note'].'</th><th colspan=3 >'.$lang_w['action'].'</th></tr>';
	
	$sql = "SELECT * FROM platform_group order by group_id";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));				

	while($row_acc = pg_fetch_assoc($acc))
	{
		$result .= '				<tr>';
							
		$result .= '				<form name=formUpdateGroup method=POST action="PlatformGroup.php?func_page=main">';
		$result .= "				<td>".$row_acc['group_id']."</td>";
		$result .= "				<input type=hidden name=update_group[id] value='".$row_acc['group_id']."'>";
		$result .= "				<td><input type=text name=update_group[name] value='".$row_acc['group_name']."'></td>";	
		$result .= "				<td><input type=text size=35 name=update_group[note] value='".$row_acc['note']."'></td>";
		$result .= '				<input type=hidden name=actions value=update_group>';
		$result .= '				<td><input type=submit value="'.$lang_w['edit'].'"></td>';
		$result .= '				</form>';

		$result .= '				<form name=formMemberGroup method=POST action="PlatformGroup.php?func_page=edit_member">';
		$result .= "				<input type=hidden name=group_id value='".$row_acc['group_id']."'>";
		$result .= '				<td><input type=submit value="'.$lang_w['member'].'"></td>';				
		$result .= '				</form>';
	
		$result .= '				<form name=formAccessGroup method=POST action="PlatformGroup.php?func_page=edit_access">';
		$result .= "				<input type=hidden name=group_id value='".$row_acc['group_id']."'>";
		$result .= '				<td><input type=submit value="'.$lang_w['access'].'"></td>';
		$result .= '				</form>';

		$result .= '				<form name=formDeleteGroup method=POST action="PlatformGroup.php?func_page=main">';
		$result .= "				<input type=hidden name=group_id value='".$row_acc['group_id']."'>";
		$result .= '				<input type=hidden name=actions value=del_group>';
		//$result .= '				<td><input type=submit value=\'刪除\'></td>';
		$result .= '				</form>';
								
		$result .= '				</tr>';
	}
	pg_free_result ($acc);
	$result .= '				</table></td>';
	$result .= '	</table>';
	$result .= '	</div>';
	$result .= '	</fieldset>';	
	$result .= '</body>';
	$result .= '</html>';
	echo $result;
}

// 新增移除群組成員介面
function edit_member_page($group_id)
{
	global $lang_w,$lang_s;
	global $gmtool_db;
	
	$result  = '';
	$result .= '<!DOCTYPE HTML>';
	$result .= '<html>';
	$result .= '<title>PlateformManagement</title>';
	$result .= '<head>';	
	$result .= '<meta charset="utf-8" />';			
	$result .= '<link rel="stylesheet" href="../css/basic.css" />';
	$result .= '</head>';	
	$result .= '<body class="gbody">';
	$result .= '	<header>';
	$result .= $lang_w['group_member'];
	$result .= '	</header>';
	$result .= '	<hr>';
	$result .= '	<fieldset>';
	$result .= '	<legend>'.$lang_w['group_member'].'</legend>';
	$result .= '	<div class="light_table">';
	// 顯示群組資訊
		$sql = "SELECT * FROM platform_group where group_id=".$group_id.";";
		$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
		$row_acc = pg_fetch_assoc($acc);
		pg_free_result ($acc);		
	$result .= '		<table >';
	$result .= '			<tr align=center><th colspan=3>'.$lang_w['group_member'].'</th></tr>';
	$result .= '			<tr align=center><th>'.$lang_w['group_id'].'</th><th>'.$lang_w['group_name'].'</th><th>'.$lang_w['note'].'</th></tr>';	
	$result .= "			<td><input type=text size=1 name=update_group[name] value='".$row_acc['group_id']."' ReadOnly></td>";
	$result .= "			<td><input type=text name=update_group[name] value='".$row_acc['group_name']."' ReadOnly></td>";
	$result .= "			<td><input type=text size=35 name=update_group[note] value='".$row_acc['note']."' ReadOnly></td>";
	$result .= '		</table>';
	
	$result .= ' 		<table width=470>';
	$result .= '			<tr align=center><th width="45%" >'.$lang_w['member'].'</th><th width="10%">&nbsp</th><th>'.$lang_w['non_member'].'</th></tr>';	
	$result .= "			<tr align=center><td>";

	global $max_group;	
	// 移除成員
		$sql = 'SELECT * FROM platform_account where (group_id::bit('.$max_group.')<< '.($max_group-$group_id).' ) >= (1::bit('.$max_group.')<<'.($max_group-1).') and account !=\'xpecadmin\'';
		$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));	
	$result .= '			<form name=formUpdateMember method=POST action="PlatformGroup.php?func_page=edit_member">';	
	$result .= '			<select name="member[]" size="20" multiple width="40">';
	while($row_acc = pg_fetch_assoc($acc))
	{
		$result .= "			<option value=\"".$row_acc['account']."\">".$row_acc['account']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>";
	}
		pg_free_result ($acc);
	$result .= '			</select></td>';
	$result .= "			<input type=hidden name=group_id value='".$group_id."'>";
	$result .= '			<input type=hidden name=actions value=remove_member>';
	//$result .= '			<td><input type=submit name=submitRemove value=">>"><br>';
	$result .= '			<td><input type=image src="../images/move_right.gif" name=submitExport value=">>" onclick="document.formUpdateMember.submit()"><br>';
	$result .= '			</form>';
	
	// 新增成員
		$sql = 'SELECT * FROM platform_account where (group_id::bit('.$max_group.')<< '.($max_group-$group_id).' ) < (1::bit('.$max_group.')<<'.($max_group-1).') and account !=\'xpecadmin\'';	
		$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));				
	$result .= '			<form name=formUpdateMember method=POST action="PlatformGroup.php?func_page=edit_member">';
	//$result .= '			<input type=submit name=submitAdd value="<<"></td><td>';
	$result .= '			<input type=image src="../images/move_left.gif" name=submitExport value="<<" onclick="document.formUpdateMember.submit()"></td><td>';
	$result .= '			<select name="nonmember[]" size="20" multiple>';	
	while($row_acc = pg_fetch_assoc($acc))
	{
		$result .= "			<option value=\"".$row_acc['account']."\">".$row_acc['account']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>";
	}
		pg_free_result ($acc);		
	$result .= '			</select></td></tr>';
	$result .= "			<input type=hidden name=group_id value='".$group_id."'>";
	$result .= '			<input type=hidden name=actions value=add_member>';	
	$result .= '			</form>';	
	
	$result .= '		</table>';
	$result .= '	</div>';
	$result .= '	</fieldset>';
	$result .= '</body>';
	$result .= '</html>';
	echo $result;
}


// 修改權限介面
function edit_access_page($group_id)
{
	global $lang_w,$lang_s;
	global $gmtool_db;

	$result  = '';
	$result .= '<!DOCTYPE HTML>';	
	$result .= '<html>';
	$result .= '<title>PlateformManagement</title>';
	$result .= '<head>';
	$result .= '<meta charset="utf-8" />';			
	$result .= '<link rel="stylesheet" href="../css/basic.css" />';
	$result .= '</head>';	
	$result .= '<body class="gbody">';	
	$result .= '	<header>';
	$result .= $lang_w['platform_access'];
	$result .= '	</header>';
	$result .= '	<hr>';
	$result .= '	<fieldset>';
	$result .= '	<legend>'.$lang_w['platform_access'].'</legend>';
	$result .= '	<div class="light_table">';
	$result .= '<pre><font color=red size=2>'.$lang_w['platform_access_note'].'<br>';
	$result .= '<br>';
	$result .= '1) '.$lang_w['platformgroup_tip3'].'<br>';
			
	$result .= '		<table >';
	$result .= '				<tr align=center><th colspan=3>'.$lang_w['access_setting'].'</th></tr>';
	$result .= "				<tr align=center><th>".$lang_w['group_id']."</th><th>".$lang_w['group_name']."</th><th>".$lang_w['note']."</th></tr>";
	
		$sql = "SELECT * FROM platform_group where group_id=".$group_id.";";
		$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));
		$row_acc = pg_fetch_assoc($acc);
		pg_free_result ($acc);
	$result .= '		<form name=formUpdateAccess method=POST action="PlatformGroup.php?func_page=edit_access">';
	
	$result .= "				<td><input type=text size=1 name=group_id value='".$row_acc['group_id']."' ReadOnly></td>";
	$result .= "				<td><input type=text name=group_name value='".$row_acc['group_name']."' ReadOnly></td>";
	$result .= "				<td><input type=text size=35 name=note value='".$row_acc['note']."' ReadOnly></td>";
	$result .= '		</table>';
	$result .= '		<br>';
	
	$result .= ' 		<table>';
	$result .= '				<tr align=center><th><font size=1>'.$lang_w['check'].'</font></th><th>'.$lang_w['function_name'].'</th><th>'.$lang_w['path'].'</th><th>'.$lang_w['note'].'</th></tr>';
	
		$sql = "SELECT * FROM udf_get_platform_function_data(".$_SESSION['lang'].");";	
		$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error($gmtool_db));			
	
	$counter = 0;	
	global $FunctionGroup;
		
	while($row_acc = pg_fetch_assoc($acc))
	{
		// 在有記錄的群組與功能打預設勾
			$sql1 = "SELECT * FROM platform_access where group_id=".$group_id." and function_id=".$row_acc['o_function_id'].";";
			$acc1 = pg_query( $gmtool_db, $sql1) or die(pg_last_error($gmtool_db));
		if($row_acc1 = pg_fetch_assoc($acc1))
		{$flag = "checked";}
		else {$flag = "";}
			pg_free_result ($acc1);
		
		if($FunctionGroup[$counter] != "")
		{
			$result .= "			<tr align=center><th colspan=7>".$FunctionGroup[$counter]."</th></tr>";
		}
			
		$result .= '			<tr>';			
		$result .= "			<td><input type=checkbox name=chkBox[".$row_acc['o_function_id']."] $flag></td>";
		$result .= "			<td><input type=text name=function_name value='".$row_acc['o_function_name']."' ReadOnly></td>";
		$result .= "			<td><input type=text name=code_path value='".$row_acc['o_code_path']."' ReadOnly></td>";
		$result .= "			<td><input type=text name=note value='".$row_acc['o_note']."' ReadOnly></td>";
		$result .= '			</tr>';
		$counter++;
	}
		pg_free_result ($acc);
	
	$result .= '		</table>';
	$result .= '		<br>';
	$result .= '		<input type=hidden name=actions value=update_access>';
	$result .= '		<input type=submit name=submitUpdateAccess value="'.$lang_w['confirm'].'">';
	$result .= '		</form>';
	$result .= '	</div>';
	$result .= '	</fieldset>';	
	$result .= '</body>';
	$result .= '</html>';
	echo $result;
}
