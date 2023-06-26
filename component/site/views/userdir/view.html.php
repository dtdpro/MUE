<?php

jimport( 'joomla.application.component.view');


class MUEViewUserdir extends JViewLegacy
{
	public function display($tpl = null)
	{
		$doc = JFactory::getDocument();
		$config=MUEHelper::getConfig();
		$doc->addScript('https://maps.googleapis.com/maps/api/js?key='.$config->gm_jsapi_key.'&sensor=false');
		$numsubs=count(MUEHelper::getUserSubs());
		$this->params	= JFactory::getApplication()->getParams('com_mue');
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
		if (!$canview) {
			$app->enqueueMessage("Subscription Requried");
			$app->redirect('index.php?option=com_mue&view=user&layout=profile');
		}
		else {
			$model = $this->getModel();
			$this->sfields = $model->getSearchFields();
			parent::display($tpl);
		}
	}
	
	
}
?>
