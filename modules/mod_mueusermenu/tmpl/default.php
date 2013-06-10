<?php

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
$config = MUEHelper::getConfig();
?>

<?php if ($user->id) : ?>

	
	
		<ul class="<?php echo $params->get('menu_class', 'menu menu-line'); ?>">
			<li class="level1">
				<a href="<?php echo JRoute::_('index.php?option=com_mue&view=user&layout=profile'); ?>" class="level1"><?php echo JText::_('MOD_MUEUSERMENU_PROFILE'); ?></a>
			</li>
			<?php  if ($config->subscribe) : ?>
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_mue&view=user&layout=subs'); ?>"><?php echo JText::_('MOD_MUEUSERMENU_SUBS'); ?></a>
			</li>
			<?php endif; ?>
			<?php  if ($params->get('mcme_installed', 0)) : ?>
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_mue&view=user&layout=cerecords'); ?>"><?php echo JText::_('MOD_MUEUSERMENU_CERECORDS'); ?></a>
			</li>
			<?php endif; ?>
			<li class="level1">
				<a href="<?php echo JRoute::_('index.php?option=com_mue&view=login&layout=logout'); ?>" class="level1"><?php echo JText::_('MOD_MUEUSERMENU_LOGOUT'); ?></a>
			</li>

		</ul>
		

<?php else : ?>

	
		
		<ul class="<?php echo $params->get('menu_class', 'menu menu-line'); ?>">
			<li class="level1">
				<a href="<?php echo JRoute::_('index.php?option=com_mue&view=login&layout=login'); ?>" class="level1"><?php echo JText::_('MOD_MUEUSERMENU_LOGIN'); ?></a>
			</li>
			<?php
			$usersConfig = JComponentHelper::getParams('com_users');
			if ($usersConfig->get('allowUserRegistration')) : ?>
			<li class="level1">
				<a href="<?php echo JRoute::_('index.php?option=com_mue&view=userreg'); ?>" class="level1"><?php echo JText::_('MOD_MUEUSERMENU_REGISTER'); ?></a>
			</li>
			<?php endif; ?>
		</ul>
		
		
	
<?php endif; ?>