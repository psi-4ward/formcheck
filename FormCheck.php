<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  4ward.media 2010
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @package    formcheck
 * @license    LGPL 
 * @filesource
 */


class FormCheck extends Frontend
{
	public function patchFormField($objWidget, $intForm)
	{
		if ($objWidget instanceof uploadable)
			return $objWidget;
			
		// Load javascript + css
		$GLOBALS['TL_CSS']['formcheck'] = 'plugins/formcheck/theme/classic/formcheck.css';
		$GLOBALS['TL_JAVASCRIPT']['formcheck'] = 'plugins/formcheck/formcheck.js';
		$GLOBALS['TL_JAVASCRIPT']['formcheck.lang'] = 'plugins/formcheck/lang/en.js';
		
		if (file_exists( TL_ROOT . '/plugins/formcheck/lang/' . $GLOBALS['TL_LANGUAGE'] . '.js'))
			$GLOBALS['TL_JAVASCRIPT']['formcheck.lang'] = 'plugins/formcheck/lang/' . $GLOBALS['TL_LANGUAGE'] . '.js';
			
			
		// add form id to initialization routine
		if (!isset($GLOBALS['FORMCHECK'][$intForm]))
		{
			$objForm = $this->Database->prepare("SELECT * FROM tl_form WHERE formID=?")->execute(str_replace('auto_', '', $intForm));
			if (!$objForm->numRows)
			{
				if(strpos($intForm,'auto_form_') !== false)
				{	// formauto formular
					$GLOBALS['FORMCHECK'][$intForm] = 'f'.str_replace('auto_form_', '', $intForm);
				}
				else 
				{
					// other formular, e.g. from Formular-class
					$GLOBALS['FORMCHECK'][$intForm] = $intForm;
				}
			}
			else
			{
				// Standardform from formular-generator
				$GLOBALS['FORMCHECK'][$intForm] = 'f'.$objForm->id;
			}
		}
			
		
		$arrFormCheck = array();
		
		// Mandatory
		if ($objWidget->mandatory)
		{
			$arrFormCheck[] = 'required';
		}
		
		// Input length
		if ($objWidget->minlength)
		{
			$arrFormCheck[] = '	[' . $objWidget->minlength . ',' . ($objWidget->maxlength ? $objWidget->maxlength : '-1') . ']';
		}
		
		// Password validateion
		if ($objWidget instanceof FormPassword)
		{
			$arrFormCheck[] = 'confirm[' . $objWidget->name . ']';
		}
		
		// Input validation
		if ($objWidget->rgxp)
		{
			switch( $objWidget->rgxp )
			{
				// allows numeric characters only
				case 'digit':
					$arrFormCheck[] = 'number';
					break;
					
				// allows alphabetic characters only
				case 'alpha':
					$arrFormCheck[] = 'alpha';
					break;
					
				// allows alphanumeric characters only
				case 'alnum':
					$arrFormCheck[] = 'alphanum';
					break;
					
				// expects a valid phone number
				case 'phone':
					$arrFormCheck[] = 'phone';
					break;
					
				// expects a valid e-mail address
				case 'email':
					$arrFormCheck[] = 'email';
					break;
					
				// expects a valid URI string
				case 'url':
					$arrFormCheck[] = 'url';
					break;
					
				// allows numbers between 0 and 100
				case 'prcnt':
					$arrFormCheck[] = '%formcheckPercent';
					break;
					
				// disallows #&()/>=<
				case 'extnd':
					$arrFormCheck[] = 'extnd';
					break;
					
				// expects a valid date format
				case 'date':
					$arrFormCheck[] = 'date';
					break;
					
				// expects a valid date and time format
				case 'datim':
					$arrFormCheck[] = 'datim';
					break;
					
				// expects a valid time format
				case 'time':
					$arrFormCheck[] = 'time';
					break;
 
			}
		}
		
		if (count($arrFormCheck))
		{
			$objWidget->class = " validate['" . implode("','", $arrFormCheck) . "']";
		}
		
		return $objWidget;
	}
	
	
	public function outputTemplate($strBuffer)
	{
		$strFormCheck = '';
		
		$arrFormIds = array();
		$GLOBALS['FORMCHECK'] = array_unique($GLOBALS['FORMCHECK']);
		
		foreach( $GLOBALS['FORMCHECK'] as $formId )
		{
			$arrFormIds[] = $formId;
		}
		
		
		// Support registration form
		if (strpos($strBuffer, 'name="FORM_SUBMIT" value="tl_registration"') !== false)
		{
			$strFormCheck .= "$$('.mod_registration form').setProperty('id', 'tl_registration');\n";
			$arrFormIds[] = 'tl_registration';
		}
		
		// Support personal-data form
		if (strpos($strBuffer, 'name="FORM_SUBMIT" value="tl_member_') !== false)
		{
			$strFormCheck .= "$$('.mod_personalData form').setProperty('id', 'tl_personalData');\n";
			$arrFormIds[] = 'tl_personalData';
		}
		
		
		if (count($arrFormIds))
		{
/* Inserting is done while loadFormField, dont know why Andreas does it here again.			
			// Inject CSS + JS files
			if (strpos($strBuffer, 'formcheck.js') === false)
			{
				$strBuffer = str_replace('</head>', '
<link rel="stylesheet" href="plugins/formcheck/theme/classic/formcheck.css" type="text/css" media="all" />
<script type="text/javascript" src="plugins/formcheck/formcheck.js"></script>
' . (file_exists( TL_ROOT . '/plugins/formcheck/lang/' . $GLOBALS['TL_LANGUAGE'] . '.js') ? '<script type="text/javascript" src="plugins/formcheck/lang/' . $GLOBALS['TL_LANGUAGE'] . '.js"></script>' : '') . '
</head>
', $strBuffer); 
			}
*/			
			
			$objDate = new Date();
			$strDateRegex = '/'. $objDate->getRegexp($GLOBALS['TL_CONFIG']['dateFormat']) .'/i';
			$strDatimRegex = '/'. $objDate->getRegexp($GLOBALS['TL_CONFIG']['datimFormat']) .'/i';
			$strTimeRegex = '/'. $objDate->getRegexp($GLOBALS['TL_CONFIG']['timeFormat']) .'/i';
			
			foreach( $arrFormIds as $formId )
			{
				$strFormCheck .= "
new FormCheck('" . $formId . "', {
	alerts : {
		date: '" . sprintf($GLOBALS['TL_LANG']['ERR']['date'], $objDate->getInputFormat($GLOBALS['TL_CONFIG']['dateFormat'])) . "',
		datim: '" . sprintf($GLOBALS['TL_LANG']['ERR']['date'], $objDate->getInputFormat($GLOBALS['TL_CONFIG']['datimFormat'])) . "',
		time: '" . sprintf($GLOBALS['TL_LANG']['ERR']['date'], $objDate->getInputFormat($GLOBALS['TL_CONFIG']['timeFormat'])) . "'
	},
	regexp : {
		date: '" . $strDateRegex . "',
		datim: '" . $strDatimRegex . "',
		time: '" . $strTimeRegex . "',
		phone: '/^[\d \+\(\)\/-]*$/'
	}
});";
			}
			
			$strBuffer = str_replace("</body>", '
<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent(\'domready\', function()
{
' . $strFormCheck . '
});
//--><!]]>
</script>
</body>', $strBuffer);
		}
		
		
		// Fix label class problem
		$strBuffer = preg_replace('@(<label.*?class=".*?)\[[^"]*@', '$1$2', $strBuffer);
		
		return $strBuffer;
	}
}