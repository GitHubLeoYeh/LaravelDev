<?php
	session_start();
	ini_set( "memory_limit", "512M" );	
	
	// GMTool 設定檔位置
	if(preg_match('/\/(admin|login)$/', dirname($_SERVER['PHP_SELF'])))
	{
					define("GMTOOL_ROOT", "../");
	        $ini_path = "../../config/gmtool.ini";
	}
	else
	{
					define("GMTOOL_ROOT", "../gmtool/");
	        $ini_path = "../config/gmtool.ini";
	}	

	require_once('global_funcs.php');

	// 讀取gmtool 設定	
	$gmtool_setting = parse_ini_file($ini_path, true);
	require_once('global_setting.php');	
	
	if(!$gmtool_setting)
	{
		echo "<font color=red>cannot find config.ini for gmtool</font>";
		exit;
	}
	// 連結平台主要DB	
	$gmtool_db_array = array(
	        "DB_TYPE"=> $gmtool_setting['database']['type'],
	        "DB_HOST"=> $gmtool_setting['database']['ip'],
	        "DB_PORT"=> $gmtool_setting['database']['port'],
	        "DB_USER"=> $gmtool_setting['database']['user'],
	        "DB_PASS"=> $gmtool_setting['database']['pwd'],
	        "DB_NAME"=> $gmtool_setting['database']['name']
	);
	$gmtool_db = get_db_connect($gmtool_db_array);
	
	// 連結MasterLogin, 使用者登入主要DB
	$sql = "SELECT * FROM platform_db where db_group_id='".$ml_id."' order by db_name";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error());			
	if($row_acc = pg_fetch_assoc($acc))
	{
		$ml_db_array = array(
		        "DB_TYPE"=> $row_acc['db_type'],
		        "DB_HOST"=> $row_acc['db_ip'],
		        "DB_PORT"=> $row_acc['db_port'],
		        "DB_USER"=> $row_acc['db_user'],
		        "DB_PASS"=> $row_acc['db_pwd'],
		        "DB_NAME"=> $row_acc['db_name']
		);
		$ml_db = get_db_connect($ml_db_array);
	}
	pg_free_result ($acc);
	// 把遊戲群組(ML Server)名稱寫入路徑
	$gameaccount_filepath .= $row_acc['db_ip'];

	// 連結NameServer, 查看使用者角色與Server用DB
	$sql = "SELECT * FROM platform_db where db_group_id='".$ns_id."' order by db_name";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error());			
	if($row_acc = pg_fetch_assoc($acc))
	{
		$ns_db_array = array(
		        "DB_TYPE"=> $row_acc['db_type'],
		        "DB_HOST"=> $row_acc['db_ip'],
		        "DB_PORT"=> $row_acc['db_port'],
		        "DB_USER"=> $row_acc['db_user'],
		        "DB_PASS"=> $row_acc['db_pwd'],
		        "DB_NAME"=> $row_acc['db_name']
		);
		$ns_db = get_db_connect($ns_db_array);
	}
	pg_free_result ($acc);


	// 連結Global_Data 管理遊戲用DB
	$sql = "SELECT * FROM platform_db where db_group_id='".$gd_id."' order by db_name";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error());			
	if($row_acc = pg_fetch_assoc($acc))
	{
		$gd_db_array = array(
		        "DB_TYPE"=> $row_acc['db_type'],
		        "DB_HOST"=> $row_acc['db_ip'],
		        "DB_PORT"=> $row_acc['db_port'],
		        "DB_USER"=> $row_acc['db_user'],
		        "DB_PASS"=> $row_acc['db_pwd'],
		        "DB_NAME"=> $row_acc['db_name']
		);
		$gd_db = get_db_connect($gd_db_array);
	}
	pg_free_result ($acc);	

	// 連結Game Billing 管理遊戲用DB
	$sql = "SELECT * FROM platform_db where db_group_id='".$gb_id."' order by db_name";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error());			
	if($row_acc = pg_fetch_assoc($acc))
	{
		$gb_db_array = array(
		        "DB_TYPE"=> $row_acc['db_type'],
		        "DB_HOST"=> $row_acc['db_ip'],
		        "DB_PORT"=> $row_acc['db_port'],
		        "DB_USER"=> $row_acc['db_user'],
		        "DB_PASS"=> $row_acc['db_pwd'],
		        "DB_NAME"=> $row_acc['db_name']
		);
		$gb_db = get_db_connect($gb_db_array);
	}
	pg_free_result ($acc);	
	
	// 取得各世界的資訊
	$world_id_cname[0] = $lang_w['all_server'];
	if($ml_db)
	{
			$sql = "SELECT * FROM aaa_config_group order by id asc";
			$acc = pg_query($ml_db, $sql) or  die(pg_last_error());
			$world_count = pg_num_rows($acc);
			for($i=0;$i < $world_count;$i++)
			{
					$row_acc = pg_fetch_assoc($acc);					
					$world_id[$i] = $row_acc['id'];
					$world_ename[$i] = $row_acc['server_ename'];
					$world_cname[$i] = $row_acc['server_cname'];
					$world_id_cname[$world_id[$i]] = $row_acc['server_cname'];
			}
			pg_free_result ($acc);
	}
	
	
	// 連結各遊戲世界的角色資料
	$sql = "SELECT * FROM platform_db where db_group_id=".$bd_gid." order by db_server_id,db_name ";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error());
	$bds_count = pg_num_rows($acc);
	
	for($i=0;$i < $bds_count;$i++)
	{
		$row_acc = pg_fetch_assoc($acc);
		$bds_db_array[$row_acc['db_server_id']] = array(
		        "DB_TYPE"=> $row_acc['db_type'],
		        "DB_HOST"=> $row_acc['db_ip'],
		        "DB_PORT"=> $row_acc['db_port'],
		        "DB_USER"=> $row_acc['db_user'],
		        "DB_PASS"=> $row_acc['db_pwd'],
		        "DB_NAME"=> $row_acc['db_name']
		        
		);
		$bds_db[$row_acc['db_server_id']] = get_db_connect($bds_db_array[$row_acc['db_server_id']]);
	}	
	pg_free_result ($acc);

	// 連結各遊戲世界的歷程記錄
	$sql = "SELECT * FROM platform_db where db_group_id=".$gl_gid." order by db_server_id,db_name ";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error());
	$gls_count = pg_num_rows($acc);
	
	for($i=0;$i < $gls_count;$i++)
	{
		$row_acc = pg_fetch_assoc($acc);
		$gls_db_array[$row_acc['db_server_id']] = array(
		        "DB_TYPE"=> $row_acc['db_type'],
		        "DB_HOST"=> $row_acc['db_ip'],
		        "DB_PORT"=> $row_acc['db_port'],
		        "DB_USER"=> $row_acc['db_user'],
		        "DB_PASS"=> $row_acc['db_pwd'],
		        "DB_NAME"=> $row_acc['db_name']
		);		
		$gls_db[$row_acc['db_server_id']] = get_db_connect($gls_db_array[$row_acc['db_server_id']]);
	}	
	pg_free_result ($acc);

	// 連結各遊戲世界的交易所歷程記錄
	$sql = "SELECT * FROM platform_db where db_group_id=".$gt_gid." order by db_server_id,db_name ";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error());
	$gts_count = pg_num_rows($acc);
	
	for($i=0;$i < $gts_count;$i++)
	{
		$row_acc = pg_fetch_assoc($acc);
		$gts_db_array[$row_acc['db_server_id']] = array(
		        "DB_TYPE"=> $row_acc['db_type'],
		        "DB_HOST"=> $row_acc['db_ip'],
		        "DB_PORT"=> $row_acc['db_port'],
		        "DB_USER"=> $row_acc['db_user'],
		        "DB_PASS"=> $row_acc['db_pwd'],
		        "DB_NAME"=> $row_acc['db_name']
		);		
		$gts_db[$row_acc['db_server_id']] = get_db_connect($gts_db_array[$row_acc['db_server_id']]);
	}	
	pg_free_result ($acc);	

	// 連結各遊戲世界的排行榜歷程記錄
	$sql = "SELECT * FROM platform_db where db_group_id=".$rank_gid." order by db_server_id,db_name ";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error());
	$ranks_count = pg_num_rows($acc);
	
	for($i=0;$i < $ranks_count;$i++)
	{
		$row_acc = pg_fetch_assoc($acc);
		$ranks_db_array[$row_acc['db_server_id']] = array(
		        "DB_TYPE"=> $row_acc['db_type'],
		        "DB_HOST"=> $row_acc['db_ip'],
		        "DB_PORT"=> $row_acc['db_port'],
		        "DB_USER"=> $row_acc['db_user'],
		        "DB_PASS"=> $row_acc['db_pwd'],
		        "DB_NAME"=> $row_acc['db_name']
		);		
		$rank_db[$row_acc['db_server_id']] = get_db_connect($ranks_db_array[$row_acc['db_server_id']]);
	}	
	pg_free_result ($acc);
	
	// 取得各Master VM IP & Port
	$sql = "SELECT * FROM platform_server where s_group_id=".$m_vm_id." order by s_index";
	$acc = pg_query( $gmtool_db, $sql) or die(pg_last_error());
	$m_vm_count = pg_num_rows($acc);
	
	for($i=0;$i < $m_vm_count;$i++)
	{
		$row_acc = pg_fetch_assoc($acc);
		$m_vm_server[$row_acc['s_server_id']] = array(		        
		        "id"=> $row_acc['s_server_id'],
		        "name"=> $row_acc['s_server_name'],
		        "ip"=> $row_acc['s_ip'],
		        "port"=> $row_acc['s_port']		        
		);
	}	
	pg_free_result ($acc);
	
	// 取得角色資料頁面連結	(StatusFrame.php位置)
	$sql = "SELECT * FROM udf_get_platform_function_data(1) WHERE o_function_type='2';";
	$acc = pg_query( $gmtool_db, $sql);
	if($row_acc = pg_fetch_assoc($acc))
	{
			$char_page = '../..'.$row_acc['o_code_path'];
	}
	pg_free_result ($acc);	
	

		// 檢查登入是否過期
		check_expire();	
		// 檢查是否在IP Connection的清單中
		check_connection_auth(get_real_ip());
		// 檢查權限	
	

		
		$access_level = get_access_level($_SERVER['PHP_SELF']);
	
		// 功能頁面
		if ( isset($_GET['func_page']) ) { $func_pageStr=$_GET['func_page'];
		} else if (isset($_POST['func_page']) ) { $func_pageStr=$_POST['func_page'];
		} else { $func_pageStr=""; }
		
		// 功能
		if ( isset($_GET['actions']) ) { $actionStr=$_GET['actions'];
		} else if (isset($_POST['actions']) ) { $actionStr=$_POST['actions'];
		} else { $actionStr=""; }		
			
		
?>
