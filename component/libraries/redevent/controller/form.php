<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008-2016 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Controller form.
 *
 * @since  1.0
 */
abstract class RedeventControllerForm extends RControllerForm
{
	/**
	 * Method called to save a model state
	 *
	 * @return  void
	 */
	public function saveModelState()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$returnUrl = $input->get('return', 'index.php');

		if ($model = $input->get('model', null))
		{
			$returnUrl = $input->get('return', 'index.php');
			$returnUrl = base64_decode($returnUrl);

			$model = RModel::getAdminInstance(ucfirst($model));
			$model->getState();
		}

		$app->redirect($returnUrl);
	}
}
