<?php
/**
 * MUE Content plugin for Joomla! 3.5+
 * Version: 1.0.2
 * @license http://www.gnu.org/licenses/gpl.html GNU/GPL v2.0.
 * @by DtD Productions
 * @Copyright (C) 2008-2018
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class  plgContentMue extends JPlugin
{
	function __construct( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}

	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();

		$user = JFactory::getUser();
		
		$uid=$user->id;
		if ($uid) {
			$userinfo = $this->getUserInfo($user);
			$username=$user->username;
			$usersname=$user->name;
			$email=$user->email;
		} else {
			$username='Guest';
			$usersname='Guest';
			$email='Guest';
			
		}
		
		//User ID
		$article->text = str_replace('{mgetuid}',$uid,$article->text);
		//Username
		$article->text = str_replace('{mgetuser}',$username,$article->text);
		//Users Name
		$article->text = str_replace('{mgetuname}',$usersname,$article->text);
		//Users Name
		$article->text = str_replace('{mgetueml}',$email,$article->text);


		if ($uid) {
			// {mussubsince} - Sub since, {mussubexp} - MUE Sub exp, {mussubdaysleft} - MUS SUb Days Left, {mue_filedname) - MUE Field with fieldname
			$article->text = str_replace( '{mussubsince}', $userinfo['userg_subsince'], $article->text );
			$article->text = str_replace( '{mussubexp}', $userinfo['userg_subexp'], $article->text );
			$article->text = str_replace( '{mussubdaysleft}', $userinfo['daysLeft'], $article->text );
			foreach ( $userinfo as $uk => $ud ) {
				$article->text = str_replace( '{mue_' . $uk . '}', $ud, $article->text );
			}

			// {muedata}
			$article->text = str_replace('{muedata}',print_r($userinfo,true),$article->text);
		}
	}

	public function getUserInfo($joomla_user) {
		$user = array();
		$userid = $joomla_user->id;
		$db = JFactory::getDBO();
		$query = 'SELECT ug.*, g.ug_name AS userGroupName FROM #__mue_usergroup as ug ';
		$query.= 'RIGHT JOIN #__mue_ugroups AS g ON ug.userg_group = g.ug_id ';
		$query.= 'WHERE ug.userg_user="'.$userid.'"';
		$db->setQuery($query); $groupdata=$db->loadAssoc();
		foreach ($groupdata as $gk => $gd) {
			$user[$gk]=$gd;
		}

		$qsub = 'SELECT DATEDIFF(DATE(DATE_ADD(usrsub_end, INTERVAL 1 Day)), DATE(NOW())) AS daysLeft FROM #__mue_usersubs as s ';
		$qsub.= 'LEFT JOIN #__mue_subs AS p ON s.usrsub_sub = p.sub_id ';
		$qsub.= 'WHERE s.usrsub_status IN ("completed","accepted") && s.usrsub_end >= DATE(NOW()) && s.usrsub_user="'.$userid.'" ';
		$qsub.= 'ORDER BY daysLeft DESC, s.usrsub_end DESC, s.usrsub_time DESC LIMIT 1';
		$db->setQuery($qsub);
		$sub = $db->loadObject();
		if ($sub) $user['daysLeft'] = $sub->daysLeft;
		else $user['daysLeft'] = 0;

		$qd = 'SELECT f.*,u.usr_data FROM #__mue_uguf as g';
		$qd.= ' RIGHT JOIN #__mue_ufields as f ON g.uguf_field = f.uf_id';
		$qd.= ' RIGHT JOIN #__mue_users as u ON u.usr_field = f.uf_id && usr_user = '.$userid;
		$qd.= ' WHERE g.uguf_group='.$user['userg_group'];
		$db->setQuery( $qd );
		$udata = $db->loadObjectList();
		foreach ($udata as $u) {
			if (!$u->uf_cms) {
				$fn=$u->uf_sname;
				if ($u->uf_type == 'multi' || $u->uf_type == 'dropdown' || $u->uf_type == 'mcbox' || $u->uf_type == 'mlist') {
					if ($u->usr_data) {
						$ansarr=explode(" ",$u->usr_data);
						$q = 'SELECT opt_text FROM #__mue_ufields_opts WHERE opt_id IN('.implode(",",$ansarr).')';
						$db->setQuery($q);
						$user[$fn] = implode(", ",$db->loadColumn());
					} else {
						$user[$fn] = "";
					}
				} else if ($u->uf_type == 'cbox' || $u->uf_type == 'yesno') {
					$user[$fn] = ($u->usr_data == "1") ? "Yes" : "No";
				} else if ($u->uf_type == 'birthday') {
					$user[$fn] = date("F j",strtotime('2000-'.substr($u->usr_data,0,2)."-".substr($u->usr_data,2,2).''));
				} else{
					$user[$fn]=$u->usr_data;
				}
			}
		}
		return $user;
	}


}


?>
