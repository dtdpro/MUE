<?php

jimport( 'joomla.application.component.view');


class MUEViewUserDir extends JView
{
	public function display($tpl = null)
	{
		$doc = &JFactory::getDocument();
		$doc->addScript('http://maps.googleapis.com/maps/api/js?sensor=false');
		$config=MUEHelper::getConfig();
		$numsubs=count(MUEHelper::getUserSubs());
		$canview=true;
		if ($config->subscribe && $config->usrdir_sub) {
			if ($numsubs) {
				$sub=MUEHelper::getActiveSub();
				if (!$sub) {
					$canview=false;
				} 
			} else {
				$canview=false;
			}
		}
		$app=Jfactory::getApplication();
		if (!$canview) $app->redirect('index.php?option=com_mue&view=user&layout=profile',"Subscription Requried");
		else {
			$model =& $this->getModel();
			$this->sfields = $model->getSearchFields();
			parent::display($tpl);
		}
	}
	
	
}
?>
