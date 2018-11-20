<?php
/** 
 * @package   	cleverdine
 * @subpackage 	com_cleverdine
 * @author    	Snowpeak Labs // Wood Box Media
 * @copyright 	Copyright (C) 2018 Wood Box Media. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link 		https://woodboxmedia.co.uk
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * restaurants View
 */
class cleverdineViewcoupons extends JViewUI {
	/**
	 * Restaurants view display method
	 * @return void
	 */
	function display($tpl = null) {

		$mainframe 	= JFactory::getApplication();
		$input 		= $mainframe->input;
		$dbo 		= JFactory::getDbo();

		RestaurantsHelper::load_css_js();
		RestaurantsHelper::load_complex_select();

		// Set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['group'] 		= $mainframe->getUserStateFromRequest('vrcoupon.group', 'group', '', 'string');
		$filters['keysearch'] 	= $mainframe->getUserStateFromRequest('vrcoupon.keysearch', 'keysearch', '', 'string');

		$ordering = OrderingManager::getColumnToOrder('coupons', 'id', 1);

		$where_claus = '';
		if( strlen($filters['group']) ) {
			$filters['group'] = intval($filters['group']);
			$where_claus = 'WHERE `group`='.$filters['group'];
		}

		if( strlen($filters['keysearch']) ) {
			if( strlen($where_claus) ) {
				$where_claus .= ' AND ';
			} else {
				$where_claus = 'WHERE ';
			}

			$where_claus .= "`code` LIKE ".$dbo->quote("%".$filters['keysearch']."%");
		}

		//db object
		$lim 	= $mainframe->getUserStateFromRequest('com_cleverdine.limit', 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 	= $mainframe->getUserStateFromRequest('vrcoupon.limitstart', 'limitstart', 0, 'uint');
		$navbut = "";

		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__cleverdine_coupons` $where_claus 
		ORDER BY `".$ordering['column']."` ".(($ordering['type'] == 2 ) ? 'DESC' : 'ASC');

		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();
		if ($dbo->getNumRows() > 0 ) {
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut="<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		} else {
			$rows = array();
		}

		$new_type = OrderingManager::getSwitchColumnType( 'coupons', $ordering['column'], $ordering['type'], array( 1, 2 ) );
		$ordering = array( $ordering['column'] => $new_type );

		$this->rows 	= &$rows;
		$this->lim0 	= &$lim0;
		$this->navbut 	= &$navbut;
		$this->ordering = &$ordering;
		$this->filters 	= &$filters;
		
		// Display the template (default.php)
		parent::display($tpl);

	}

	/**
	 * Setting the toolbar
	 */
	private function addToolBar() {
		//Add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWCOUPONS'), 'restaurants');
		
		if (JFactory::getUser()->authorise('core.create', 'com_cleverdine')) {
			JToolbarHelper::addNew('newcoupon', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_cleverdine')) {
			JToolbarHelper::editList('editcoupon', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_cleverdine')) {
			JToolbarHelper::deleteList( '', 'deleteCoupons', JText::_('VRDELETE'));
		}
		
	}

}
?>