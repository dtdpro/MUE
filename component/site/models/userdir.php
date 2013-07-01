<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

class MUEModelUserDir extends JModelLegacy
{
	function getSearchFields() {
		$db =& JFactory::getDBO();
		$qd = 'SELECT f.* FROM #__mue_ufields as f';
		$qd.= ' WHERE f.published = 1';
		$qd.=" && f.uf_hidden = 0";
		$qd.=" && f.uf_userdir = 1";
		$qd .= ' && f.uf_type != "captcha"';
		$qd.= ' ORDER BY f.ordering';
		$db->setQuery( $qd );
		$ufields = $db->loadObjectList();
		foreach ($ufields as &$f) {
			switch ($f->uf_type) {
				case 'multi':
				case 'dropdown':
				case 'mcbox':
				case 'mlist':
					$qo = 'SELECT opt_id as value, opt_text as text FROM #__mue_ufields_opts WHERE opt_field='.$f->uf_id.' && published > 0 ORDER BY ordering';
					$this->_db->setQuery($qo);
					$f->options = $this->_db->loadObjectList();
					break;
			}
		}
		return $ufields;
	}
	
	

}
