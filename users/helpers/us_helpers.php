<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
// UserSpice Specific Functions
require_once $abs_us_root.$us_url_root.'usersc/includes/custom_functions.php';
require_once $abs_us_root.$us_url_root.'usersc/includes/analytics.php';

function testUS(){
	echo "<br>";
	echo "UserSpice Functions have been properly included";
	echo "<br>";
}


function get_gravatar($email, $s = 120, $d = 'mm', $r = 'pg', $img = false, $atts = array() ) {
	$url = 'https://www.gravatar.com/avatar/';
	$url .= md5( strtolower( trim( $email ) ) );
	$url .= "?s=$s&d=$d&r=$r";
	if ( $img ) {
		$url = '<img src="' . $url . '"';
		foreach ( $atts as $key => $val )
		$url .= ' ' . $key . '="' . $val . '"';
		$url .= ' />';
	}
	return $url;
}

//Check if a permission level ID exists in the DB
function permissionIdExists($id) {
	$db = DB::getInstance();
	$query = $db->query("SELECT id FROM permissions WHERE id = ? LIMIT 1",array($id));
	$num_returns = $query->count();

	if ($num_returns > 0) {
		return true;
	} else {
		return false;
	}
}

//Check if a user ID exists in the DB
function userIdExists($id) {
	$db = DB::getInstance();
	$query = $db->query("SELECT * FROM users WHERE id = ?",array($id));
	$num_returns = $query->count();
	if ($num_returns > 0){
		return true;
	}else{
		return false;
	}
}

//Retrieve information for a single permission level
function fetchPermissionDetails($id) {
	$db = DB::getInstance();
	$query = $db->query("SELECT id, name FROM permissions WHERE id = ? LIMIT 1",array($id));
	$results = $query->first();
	$row = array('id' => $results->id, 'name' => $results->name);
	return ($row);
}

//Change a permission level's name
function updatePermissionName($id, $name) {
	$db = DB::getInstance();
	$fields=array('name'=>$name);
	$db->update('permissions',$id,$fields);
}

//Checks if a username exists in the DB
function usernameExists($username)   {
	$db = DB::getInstance();
	$query = $db->query("SELECT * FROM users WHERE username = ?",array($username));
	$results = $query->results();
	return ($results);
}

//Retrieve information for all users
function fetchAllUsers($orderBy=[], $desc=[]) {
	$db = DB::getInstance();
	if(!empty($orderBy)){
		$query = $db->query("SELECT * FROM users ORDER BY $orderBy DESC");
		if ($desc === TRUE){
		}else{
			$query = $db->query("SELECT * FROM users ORDER BY $orderBy");
		}
	}else{
		$query = $db->query("SELECT * FROM users");
	}
	$results = $query->results();
	return ($results);
}

//Retrieve complete user information by username, token or ID
function fetchUserDetails($username=NULL,$token=NULL, $id=NULL){
	if($username!=NULL) {
		$column = "username";
		$data = $username;
	}elseif($id!=NULL) {
		$column = "id";
		$data = $id;
	}
	$db = DB::getInstance();
	$query = $db->query("SELECT * FROM users WHERE $column = $data LIMIT 1");
	$results = $query->first();
	return ($results);
}

//Retrieve list of permission levels a user has
function fetchUserPermissions($user_id) {
	$db = DB::getInstance();
	$query = $db->query("SELECT * FROM user_permission_matches WHERE user_id = ?",array($user_id));
	$results = $query->results();
	return ($results);
}


//Retrieve list of users who have a permission level
function fetchPermissionUsers($permission_id) {
	$db = DB::getInstance();
	$query = $db->query("SELECT id, user_id FROM user_permission_matches WHERE permission_id = ?",array($permission_id));
	$results = $query->results();
	return ($results);
	$row[$user] = array('id' => $id, 'user_id' => $user);
	if (isset($row)){
		return ($row);
	}
}

//Unmatch permission level(s) from user(s)
function removePermission($permissions, $members) {
	$db = DB::getInstance();
	if(is_array($members)){
		$memberString = '';
		foreach($members as $member){
			$memberString .= $member.',';
		}
		$memberString = rtrim($memberString,',');

		$q = $db->query("DELETE FROM user_permission_matches WHERE permission_id = ? AND user_id IN ({$memberString})",[$permissions]);
	}elseif(is_array($permissions)){
		$permissionString = '';
		foreach($permissions as $permission){
			$permissionString .= $permission.',';
		}
		$permissionString = rtrim($permissionString,',');
		$q = $db->query("DELETE FROM user_permission_matches WHERE user_id = ? AND permission_id IN ({$permissionString})",[$members]);
	}
	return $q->count();
}

//Retrieve a list of all .php files in root files folder
function getPathPhpFiles($absRoot,$urlRoot,$fullPath) {
	$directory = $absRoot.$urlRoot.$fullPath;
	//bold ($directory);
	$pages = glob($directory . "*.php");

	foreach ($pages as $page){
		$fixed = str_replace($absRoot.$urlRoot,'',$page);
		$row[$fixed] = $fixed;
	}
	return $row;
}

//Retrieve a list of all .php files in root files folder
function getPageFiles() {
	$directory = "../";
	$pages = glob($directory . "*.php");
	foreach ($pages as $page){
		$fixed = str_replace('../','/'.$us_url_root,$page);
		$row[$fixed] = $fixed;
	}
	return $row;
}

//Retrive a list of all .php files in users/ folder
function getUSPageFiles() {
	$directory = "../users/";
	$pages = glob($directory . "*.php");
	foreach ($pages as $page){
		$fixed = str_replace('../users/','/'.$us_url_root.'users/',$page);
		$row[$fixed] = $fixed;
	}
	return $row;
}

//Delete a page from the DB
function deletePages($pages) {
	$db = DB::getInstance();
	if(!$query = $db->query("DELETE FROM pages WHERE id IN ({$pages})")){
		throw new Exception('There was a problem deleting pages.');
	}else{
		return true;
	}
}

//Fetch information on all pages
function fetchAllPages() {
	$db = DB::getInstance();
	$query = $db->query("SELECT id, page, private, re_auth FROM pages ORDER BY id DESC");
	$pages = $query->results();
	//return $pages;

	if (isset($row)){
		return ($row);
	}else{
		return $pages;
	}
}

//Fetch information for a specific page
function fetchPageDetails($id) {
	$db = DB::getInstance();
	$query = $db->query("SELECT id, page, private, re_auth FROM pages WHERE id = ?",array($id));
	$row = $query->first();
	return $row;
}


//Check if a page ID exists
function pageIdExists($id) {
	$db = DB::getInstance();
	$query = $db->query("SELECT private FROM pages WHERE id = ? LIMIT 1",array($id));
	$num_returns = $query->count();
	if ($num_returns > 0){
		return true;
	}else{
		return false;
	}
}

//Toggle private/public setting of a page
function updatePrivate($id, $private) {
	$db = DB::getInstance();
	$result = $db->query("UPDATE pages SET private = ? WHERE id = ?",array($private,$id));
	return $result;
}

//Add a page to the DB
function createPages($pages) {
	$db = DB::getInstance();
	foreach($pages as $page){
		$fields=array('page'=>$page, 'private'=>'0');
		$db->insert('pages',$fields);
	}
}

//Match permission level(s) with page(s)
function addPage($page, $permission) {
	$db = DB::getInstance();
	$i = 0;
	if (is_array($permission)){
		foreach($permission as $id){
			$query = $db->query("INSERT INTO permission_page_matches (
				permission_id, page_id ) VALUES ( $id , $page )");
				$i++;
			}
		} elseif (is_array($page)){
			foreach($page as $id){
				$query = $db->query("INSERT INTO permission_page_matches (
					permission_id, page_id ) VALUES ( $permission , $id )");
					$i++;
				}
			} else {
				$query = $db->query("INSERT INTO permission_page_matches (
					permission_id, page_id ) VALUES ( $permission , $page )");
					$i++;
				}
				return $i;
			}

			//Retrieve list of permission levels that can access a page
			function fetchPagePermissions($page_id) {
				$db = DB::getInstance();
				$query = $db->query("SELECT id, permission_id FROM permission_page_matches WHERE page_id = ? ",array($page_id));
				$results = $query->results();
				return($results);
			}

			//Retrieve list of pages that a permission level can access
			function fetchPermissionPages($permission_id) {
				$db = DB::getInstance();

				$query = $db->query(
					"SELECT m.id as id, m.page_id as page_id, p.page as page, p.private as private
					FROM permission_page_matches AS m
					INNER JOIN pages AS p ON m.page_id = p.id
					WHERE m.permission_id = ?",[$permission_id]);
					$results = $query->results();
					return ($results);
				}

				//Unmatched permission and page
				function removePage($pages, $permissions) {
					$db = DB::getInstance();
					if(is_array($permissions)){
						$ids = '';
						for($i = 0; $i < count($permissions);$i++){
							$ids .= $permissions[$i].',';
						}
						$ids = rtrim($ids,',');
						if($query = $db->query("DELETE FROM permission_page_matches WHERE permission_id IN ({$ids}) AND page_id = ?",array($pages))){
							return $query->count();
						}
					}elseif(is_array($pages)){
						$ids = '';
						for($i = 0; $i < count($pages);$i++){
							$ids .= $pages[$i].',';
						}
						$ids = rtrim($ids,',');
						if($query = $db->query("DELETE FROM permission_page_matches WHERE page_id IN ({$ids}) AND permission_id = ?",array($permissions))){
							return $query->count();
						}
					}
				}

				//Delete a defined array of users
				function deleteUsers($users) {
					$db = DB::getInstance();
					$i = 0;
					foreach($users as $id){
						$query1 = $db->query("DELETE FROM users WHERE id = ?",array($id));
						$query2 = $db->query("DELETE FROM user_permission_matches WHERE user_id = ?",array($id));
						$query3 = $db->query("DELETE FROM profiles WHERE user_id = ?",array($id));
						$i++;
					}
					return $i;
				}

				// retrieve ?dest=page and check that it exists in the legitimate pages in the
				// database or is in the Config::get('whitelisted_destinations')
				function sanitizedDest($varname='dest') {
					if ($dest = Input::get($varname)) {
						// if it exists in the database then it is a legitimate destination
						$db = DB::getInstance();
						$query = $db->query("SELECT id, page, private FROM pages WHERE page = ?",[$dest]);
						$count = $query->count();
						if ($count>0){
							return $dest;
						}
						// if the administrator has intentionally whitelisted a destination it is legitimate
						if ($whitelist = Config::get('whitelisted_destinations')) {
							if (in_array($dest, (array)$whitelist)) {
								return $dest;
							}
						}
					}
					return false;
				}

				//Check if a user has access to a page
				function securePage($uri){
					//Separate document name from uri
					//$tokens = explode('/', $uri);
					//$page = end($tokens);

					$abs_us_root=$_SERVER['DOCUMENT_ROOT'];

					$self_path=explode("/", $_SERVER['PHP_SELF']);
					$self_path_length=count($self_path);
					$file_found=FALSE;

					for($i = 1; $i < $self_path_length; $i++){
						array_splice($self_path, $self_path_length-$i, $i);
						$us_url_root=implode("/",$self_path)."/";

						if (file_exists($abs_us_root.$us_url_root.'z_us_root.php')){
							$file_found=TRUE;
							break;
						}else{
							$file_found=FALSE;
						}
					}

					$urlRootLength=strlen($us_url_root);
					$page=substr($uri,$urlRootLength,strlen($uri)-$urlRootLength);

					//bold($page);

					$db = DB::getInstance();
					$id = null;
					$private = null;
					// dnd($page);
					global $user;
					// dnd($user);
					if(isset($user) && $user->data() != null){
						if($user->data()->permissions==0){
							bold('<br><br><br>Sorry. You have been banned. If you feel this is an error, please contact the administrator.');
							die();
						}
					}
					//retrieve page details
					$query = $db->query("SELECT id, page, private FROM pages WHERE page = ?",[$page]);
					$count = $query->count();
					if ($count==0){
						bold('<br><br>You must go into the Admin Panel and click the Manage Pages button to add this page to the database. Doing so will make this error go away.');
						die();
					}
					$results = $query->first();

					$pageDetails = array( 'id' =>$results->id, 'page' => $results->page, 'private' =>$results->private);

					$pageID = $results->id;
					$ip = ipCheck();
					//If page does not exist in DB, allow access
					if (empty($pageDetails)){
						return true;
					}elseif ($pageDetails['private'] == 0){//If page is public, allow access
						return true;
					}elseif(!$user->isLoggedIn()){ //If user is not logged in, deny access
						$fields = array(
							'user'	=> 0,
							'page'	=> $pageID,
							'ip'		=> $ip,
						);
						$db->insert('audit',$fields);
						require_once $abs_us_root.$us_url_root.'usersc/scripts/not_logged_in.php';
						Redirect::to($us_url_root.'users/login.php', '?dest='.$page);
						return false;
					}else {
						//Retrieve list of permission levels with access to page

						$query = $db->query("SELECT permission_id FROM permission_page_matches WHERE page_id = ?",[$pageID]);

						$permission = $query->results();
						$pagePermissions[] = $permission;

						//Check if user's permission levels allow access to page
						if (checkPermission($pagePermissions)){
							return true;
						}elseif  (in_array($user->data()->id, $master_account)){ //Grant access if master user
							return true;
						}else {
							if (!$homepage = Config::get('homepage'))
							$homepage = 'index.php';
							$fields = array(
								'user'	=> $user->data()->id,
								'page'	=> $pageID,
								'ip'		=> $ip,
							);
							$db->insert('audit',$fields);
							require_once $abs_us_root.$us_url_root.'usersc/scripts/did_not_have_permission.php';
							Redirect::to($homepage);
							return false;
						}
					}
				}

				//Does user have permission
				//This is the old school UserSpice Permission System
				function checkPermission($permission) {
					$db = DB::getInstance();
					global $user;
					//Grant access if master user
					$access = 0;

					foreach($permission[0] as $perm){
						if ($access == 0){
							$query = $db->query("SELECT id FROM user_permission_matches  WHERE user_id = ? AND permission_id = ?",array($user->data()->id,$perm->permission_id));
							$results = $query->count();
							if ($results > 0){
								$access = 1;
							}
						}
					}
					if ($access == 1){
						return true;
					}
					if ($user->data()->id == 1){
						return true;
					}else{
						return false;
					}
				}

				function checkMenu($permission, $id) {
					$db = DB::getInstance();
					global $user;
					//Grant access if master user
					$access = 0;

					if ($access == 0){
						$query = $db->query("SELECT id FROM user_permission_matches  WHERE user_id = ? AND permission_id = ?",array($id,$permission));
						$results = $query->count();
						if ($results > 0){
							$access = 1;
						}
					}
					if ($access == 1){
						return true;
					}
					if ($user->data()->id == 1){
						return true;
					}else{
						return false;
					}
				}

				//Retrieve information for all permission levels
				function fetchAllPermissions() {
					$db = DB::getInstance();
					$query = $db->query("SELECT id, name FROM permissions");
					$results = $query->results();
					return ($results);
				}

				//Displays error and success messages
				function resultBlock($errors,$successes){
					//Error block
					if(count($errors) > 0){
						echo "<div class='alert alert-danger alert-dismissible' role='alert'> <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
						<ul>";
						foreach($errors as $error){
							echo "<li>".$error."</li>";
						}
						echo "</ul>";
						echo "</div>";
					}

					//Success block
					if(count($successes) > 0){
						echo "<div class='alert alert-success alert-dismissible' role='alert'> <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
						<ul>";
						foreach($successes as $success){
							echo "<li>".$success."</li>";
						}
						echo "</ul>";
						echo "</div>";
					}
				}

				//Inputs language strings from selected language.
				function lang($key,$markers = NULL){
					global $lang;
					if($markers == NULL){
						$str = $lang[$key];
					}else{
						//Replace any dyamic markers
						$str = $lang[$key];
						$iteration = 1;
						foreach($markers as $marker){
							$str = str_replace("%m".$iteration."%",$marker,$str);
							$iteration++;
						}
					}
					//Ensure we have something to return
					if($str == ""){
						return ("No language key found");
					}else{
						return $str;
					}
				}


				//Check if a permission level name exists in the DB
				function permissionNameExists($permission) {
					$db = DB::getInstance();
					$query = $db->query("SELECT id FROM permissions WHERE
						name = ?",array($permission));
						$results = $query->results();
					}

					//Match permission level(s) with user(s)
					function addPermission($permission_ids, $members) {
						$db = DB::getInstance();
						$i = 0;
						if(is_array($permission_ids)){
							foreach($permission_ids as $permission_id){
								if($db->query("INSERT INTO user_permission_matches (user_id,permission_id) VALUES (?,?)",[$members,$permission_id])){
									$i++;
								}
							}
						}elseif(is_array($members)){
							foreach($members as $member){
								if($db->query("INSERT INTO user_permission_matches (user_id,permission_id) VALUES (?,?)",[$member,$permission_ids])){
									$i++;
								}
							}
						}
						return $i;
					}


					//Delete a permission level from the DB
					function deletePermission($permission) {
						global $errors;
						$i = 0;
						$db = DB::getInstance();
						foreach($permission as $id){
							if ($id == 1){
								$errors[] = lang("CANNOT_DELETE_NEWUSERS");
							}
							elseif ($id == 2){
								$errors[] = lang("CANNOT_DELETE_ADMIN");
							}else{
								$query1 = $db->query("DELETE FROM permissions WHERE id = ?",array($id));
								$query2 = $db->query("DELETE FROM user_permission_matches WHERE permission_id = ?",array($id));
								$query3 = $db->query("DELETE FROM permission_page_matches WHERE permission_id = ?",array($id));
								$i++;
							}
						}
						return $i;

						//Redirect::to('admin_permissions.php');
					}

					//Checks if an email is valid
					function isValidEmail($email){
						if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
							return true;
						}
						else {
							return false;
						}
					}

					//Check if an email exists in the DB
					function emailExists($email) {
						$db = DB::getInstance();
						$query = $db->query("SELECT email FROM users WHERE email = ?",array($email));
						$num_returns = $query->count();
						if ($num_returns > 0){
							return true;
						}else{
							return false;
						}
					}

					//Update a user's email
					function updateEmail($id, $email) {
						$db = DB::getInstance();
						$fields=array('email'=>$email);
						$db->update('users',$id,$fields);

						return true;
					}

					function echoId($id,$table,$column){
						$db = DB::getInstance();
						$query = $db->query("SELECT $column FROM $table WHERE id = $id LIMIT 1");
						$count=$query->count();

						if ($count > 0) {
							$results=$query->first();
							foreach ($results as $result){
								echo $result;
							}
						} else {
							echo "Not in database";
							Return false;
						}
					}

					function bin($number){
						if ($number == 0){
							echo "<strong><font color='red'>No</font></strong>";
						}
						if ($number == 1){
							echo "<strong><font color='green'>Yes</font></strong>";
						}
						if ($number != 0 && $number !=1){
							echo "<strong><font color='blue'>Other</font></strong>";
						}
					}


					function echouser($id){
						$db = DB::getInstance();
						$settingsQ = $db->query("SELECT echouser FROM settings");
						$settings = $settingsQ->first();

						if($settings->echouser == 0){
							$query = $db->query("SELECT fname,lname FROM users WHERE id = ? LIMIT 1",array($id));
							$count=$query->count();
							if ($count > 0) {
								$results=$query->first();
								echo $results->fname." ".$results->lname;
							} else {
								echo "-";
							}
						}

						if($settings->echouser == 1){
							$query = $db->query("SELECT username FROM users WHERE id = ? LIMIT 1",array($id));
							$count=$query->count();
							if ($count > 0) {
								$results=$query->first();
								echo ucfirst($results->username);
							} else {
								echo "-";
							}
						}

						if($settings->echouser == 2){
							$query = $db->query("SELECT username,fname,lname FROM users WHERE id = ? LIMIT 1",array($id));
							$count=$query->count();
							if ($count > 0) {
								$results=$query->first();
								echo ucfirst($results->username).'('.$results->fname.' '.$results->lname.')';
							} else {
								echo "-";
							}
						}

						if($settings->echouser == 3){
							$query = $db->query("SELECT username,fname FROM users WHERE id = ? LIMIT 1",array($id));
							$count=$query->count();
							if ($count > 0) {
								$results=$query->first();
								echo ucfirst($results->username).'('.$results->fname.')';
							} else {
								echo "-";
							}
						}
					}

					function echousername($id){
						$db = DB::getInstance();
						$query = $db->query("SELECT username FROM users WHERE id = ? LIMIT 1",array($id));
						$count=$query->count();
						if ($count > 0) {
							$results=$query->first();
							return ($results->username);
						} else {
							return "Unknown";
						}
					}

					function generateForm($table,$id, $skip=[]){
						$db = DB::getInstance();
						$fields = [];
						$q=$db->query("SELECT * FROM {$table} WHERE id = ?",array($id));
						$r=$q->first();

						foreach($r as $field => $value) {
							if(!in_array($field, $skip)){
								echo '<div class="form-group">';
								echo '<label for="'.$field.'">'.ucfirst($field).'</label>';
								echo '<input type="text" class="form-control" name="'.$field.'" id="'.$field.'" value="'.$value.'">';
								echo '</div>';
							}
						}
						return true;
					}

					function generateAddForm($table, $skip=[]){
						$db = DB::getInstance();
						$fields = [];
						$q=$db->query("SELECT * FROM {$table}");
						$r=$q->first();

						foreach($r as $field => $value) {
							if(!in_array($field, $skip)){
								echo '<div class="form-group">';
								echo '<label for="'.$field.'">'.ucfirst($field).'</label>';
								echo '<input type="text" class="form-control" name="'.$field.'" id="'.$field.'" value="">';
								echo '</div>';
							}
						}
						return true;
					}

					function updateFields2($post, $skip=[]){
						$fields = [];
						foreach($post as $field => $value) {
							if(!in_array($field, $skip)){
								$fields[$field] = sanitize($post[$field]);
							}
						}
						return $fields;
					}

					function hasPerm($permissions, $id) {
						$db = DB::getInstance();
						global $user;
						//Grant access if master user
						$access = 0;

						foreach($permissions as $permission){

							if ($access == 0){
								$query = $db->query("SELECT id FROM user_permission_matches  WHERE user_id = ? AND permission_id = ?",array($id,$permission));
								$results = $query->count();
								if ($results > 0){
									$access = 1;
								}
							}
						}
						if ($access == 1){
							return true;
						}
						if (in_array($user->data()->id, $master_account)){
							return true;
						}else{
							return false;
						}
					}

					function echopage($id){
						$db = DB::getInstance();
						$query = $db->query("SELECT page FROM pages WHERE id = ? LIMIT 1",array($id));
						$count=$query->count();

						if ($count > 0) {
							$results=$query->first();
							echo $results->page;
						} else {
							echo "Unknown";
						}
					}

					function mqtt($id,$topic,$message){
						//id is the server id in the mqtt_settings.php
						$db = DB::getInstance();
						$query = $db->query("SELECT * FROM mqtt WHERE id = ?",array($id));
						$count=$query->count();
						if($count > 0){
							$server = $query->first();

							$host = $server->server;
							$port = $server->port;
							$username = $server->username;
							$password = $server->password;

							$mqtt = new phpMQTT($host, $port, "ClientID".rand());

							if ($mqtt->connect(true,NULL,$username,$password)) {
								$mqtt->publish($topic,$message, 0);
								$mqtt->close();
							}else{
								echo "Fail or time out";
							}
						}else{
							echo "Server not found. Please check your id.";
						}
					}

					//Update User
					function updateUser($column, $id, $value) {
						$db = DB::getInstance();
						$result = $db->query("UPDATE users SET $column = ? WHERE id = ?",array($value,$id));
						return $result;
					}

					//Cleaning function
					function clean($string) {
						$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
						$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

						return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
					}

					function updateReAuth($id, $re_auth) {
						$db = DB::getInstance();
						$result = $db->query("UPDATE pages SET re_auth = ? WHERE id = ?",array($re_auth,$id));
						return $result;
					}

					function stripPagePermissions($id) {
						$db = DB::getInstance();
						$result = $db->query("DELETE from permission_page_matches WHERE page_id = ?",array($id));
						return $result;
					}


					function reAuth($uri,$uid){
						//Separate document name from uri
						//$tokens = explode('/', $uri);
						//$page = end($tokens);

						$abs_us_root=$_SERVER['DOCUMENT_ROOT'];

						$self_path=explode("/", $_SERVER['PHP_SELF']);
						$self_path_length=count($self_path);
						$file_found=FALSE;

						for($i = 1; $i < $self_path_length; $i++){
							array_splice($self_path, $self_path_length-$i, $i);
							$us_url_root=implode("/",$self_path)."/";

							if (file_exists($abs_us_root.$us_url_root.'z_us_root.php')){
								$file_found=TRUE;
								break;
							}else{
								$file_found=FALSE;
							}
						}

						$urlRootLength=strlen($us_url_root);
						$page=substr($uri,$urlRootLength,strlen($uri)-$urlRootLength);

						//bold($page);

						$db = DB::getInstance();
						$id = null;

						//retrieve page details
						$query = $db->query("SELECT id, page, re_auth FROM pages WHERE page = ?",[$page]);
						$count = $query->count();
						if ($count==0){
							bold('<br><br>Page not found. Something went wrong.');
							die();
						}
						$results = $query->first();

						$pageDetails = array( 'id' =>$results->id, 'page' => $results->page, 're_auth' => $results->re_auth);
						$pageID = $results->id;

						//If page does not exist in DB, allow access
						if (empty($pageDetails)){
							return true;
						}elseif ($pageDetails['re_auth'] == 0){//If page is public, allow access
							return true;
						}else{ //Authorization is required.  Insert your authorization code below.

							verifyadmin($uid,$page);

						}
					}

					function verifyadmin($id,$page) {
						$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
						$db = DB::getInstance();
						$findUserQ = $db->query("SELECT last_confirm FROM users WHERE id = ?",array($id));
						$findUser = $findUserQ->first();
						//get the current time
						$current=date("Y-m-d H:i:s");

						//convert the string time to a time format php can use
						$ctFormatted = date("Y-m-d H:i:s", strtotime($current));

						//convert the db time to a time format php can use
						$dbTime = strtotime($findUser->last_confirm);

						//take the db time and add 2 hours to it.
						$dbPlus = date("Y-m-d H:i:s", strtotime('+2 hours', $dbTime));

						//See what you've got, uncomment this
						// echo $ctFormatted;
						// echo '<br>';
						// echo $dbPlus;
						// echo '<br>';


						if (strtotime($ctFormatted) > strtotime($dbPlus)){
							Redirect::to('admin_verify.php?actual_link='.$actual_link.'&page='.$page);
						}
						else
						{
							$db = DB::getInstance();
							$db->query("UPDATE users SET last_confirm = ? WHERE id = ?",array($current,$id));
						}
					}

					function fetchUserName($username=NULL,$token=NULL, $id=NULL){
						if($username!=NULL) {
							$column = "username";
							$data = $username;
						}elseif($id!=NULL) {
							$column = "id";
							$data = $id;
						}
						$db = DB::getInstance();
						$query = $db->query("SELECT CONCAT(fname,' ',lname) AS name FROM users WHERE $column = $data LIMIT 1");
						$count = $query->count();
						if ($count > 0) {
							$results = $query->first();
							return ($results->name);
						} else {
							return "Unknown";
						}
					}

					function fetchMessageUsers() {
						$db = DB::getInstance();
						$queryUser = $db->query("SELECT * FROM users WHERE msg_exempt = 0");
						$resultsUser = $queryUser->results();
						return ($resultsUser);
					}

					function archiveThreads($threads,$user_id,$status) {
						$db = DB::getInstance();
						$i = 0;
						foreach($threads as $id){
							$query = $db->query("SELECT msg_from,msg_to FROM message_threads WHERE id = ?",array($id));
							$results = $query->first();
							if($results->msg_from == $user_id) {
								$db->query("UPDATE message_threads SET archive_from = ? WHERE id = ?",array($status,$id));
								if($status == 1) $db->query("UPDATE messages SET msg_read = ? WHERE msg_thread = ? AND msg_to = ?",array(1,$id,$user_id));
							}
							if($results->msg_to == $user_id) {
								$db->query("UPDATE message_threads SET archive_to = ? WHERE id = ?",array($status,$id));
								if($status == 1) $db->query("UPDATE messages SET msg_read = ? WHERE msg_thread = ? AND msg_to = ?",array(1,$id,$user_id));
							}
							$i++;
						}
						return $i;
					}

					function deleteThread($threads,$user_id,$status) {
						$db = DB::getInstance();
						$i = 0;
						foreach($threads as $id){
							$query = $db->query("SELECT msg_from,msg_to FROM message_threads WHERE id = ?",array($id));
							$results = $query->first();
							if($results->msg_from == $user_id) {
								$db->query("UPDATE message_threads SET hidden_from = ? WHERE id = ?",array($status,$id));
								$db->query("UPDATE messages SET msg_read = ? WHERE msg_thread = ? AND msg_to = ?",array(1,$id,$user_id));
							}
							if($results->msg_to == $user_id) {
								$db->query("UPDATE message_threads SET hidden_to = ? WHERE id = ?",array($status,$id));
								$db->query("UPDATE messages SET msg_read = ? WHERE msg_thread = ? AND msg_from = ?",array(1,$id,$user_id));
							}
							$i++;
						}
						return $i;
					}


					function messageUser($user_id,$request_user,$subject,$body) {
						$db = DB::getInstance();
						$settingsQ = $db->QUERY("SELECT * FROM settings");
						$settings = $settingsQ->first();
						$userData = $db->query("SELECT fname FROM users WHERE id = ?",array($user_id))->first();
						$date = date("Y-m-d H:i:s");

						$thread = array(
							'msg_from'    => $user_id,
							'msg_to'      => $request_user,
							'msg_subject' => $subject,
							'last_update' => $date,
							'last_update_by' => $user_id,
						);
						$db->insert('message_threads',$thread);
						$newThread = $db->lastId();


						$fields = array(
							'msg_from'    => $user_id,
							'msg_to'      => $request_user,
							'msg_body'    => $body,
							'msg_thread'  => $newThread,
							'sent_on'     => $date,
						);

						$db->insert('messages',$fields);
						$email = $db->query("SELECT fname,email,msg_notification FROM users WHERE id = ?",array($request_user))->first();
						if($settings->msg_notification == 1 && $email->msg_notification == 1) {
							$to = rawurlencode($email->email);
							$params = array(
								'fname' => $email->fname,
								'sendfname' => $userData->fname,
								'body' => $body,
								'msg_thread' => $newThread,
							);
							$to = rawurlencode($email->email);
							$emailbody = email_body('_email_msg_template.php',$params);
							email($to,$subject,$emailbody);
						}
					}
