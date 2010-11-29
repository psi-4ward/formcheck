<?php

/*************************/
/** Choose a Stylesheet **/
/*************************/

$GLOBALS['TL_CSS']['formcheck'] = 'plugins/formcheck/theme/blue/formcheck.css';
//$GLOBALS['TL_CSS']['formcheck'] = 'plugins/formcheck/theme/classic/formcheck.css';
//$GLOBALS['TL_CSS']['formcheck'] = 'plugins/formcheck/theme/green/formcheck.css';
//$GLOBALS['TL_CSS']['formcheck'] = 'plugins/formcheck/theme/grey/formcheck.css';
//$GLOBALS['TL_CSS']['formcheck'] = 'plugins/formcheck/theme/red/formcheck.css';
//$GLOBALS['TL_CSS']['formcheck'] = 'plugins/formcheck/theme/white/formcheck.css';
		


/** Dont change anything below **/

$language = (file_exists( TL_ROOT . '/plugins/formcheck/lang/' . $GLOBALS['TL_LANGUAGE'] . '.js')) ? $GLOBALS['TL_LANGUAGE'] : 'en';
$GLOBALS['FORMCHECK'] = array_unique($GLOBALS['FORMCHECK']);
if(count($GLOBALS['FORMCHECK']) <= 0) return;

$objDate = new Date();
$strDateRegex = '/'. $objDate->getRegexp($GLOBALS['TL_CONFIG']['dateFormat']) .'/i';
$strDatimRegex = '/'. $objDate->getRegexp($GLOBALS['TL_CONFIG']['datimFormat']) .'/i';
$strTimeRegex = '/'. $objDate->getRegexp($GLOBALS['TL_CONFIG']['timeFormat']) .'/i';
?>

<script type="text/javascript" src="plugins/formcheck/formcheck-yui.js"></script>
<script type="text/javascript" src="plugins/formcheck/lang/<?php echo $language?>.js"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent('domready', function(){

<?php // Remvoe validation-classes from labels ?>
$$('label').each(function(el){ el.set('class',el.get('class').replace(/validate\[[^\]]*\]/gi,'')); });

<?php // Pass validation to radio buttons ?>
$$('div.radio_container').each(function(el){
	var erg = el.get('class').match(/(validate[^"\s]+)/g);
	if(erg.length < 1) return;
	el.removeClass(erg[0]);
	el.getElement('input[type=radio]').addClass(erg[0]);
});

<?php // FormCheck config ?>
var formcheckConfig = {
	alerts : {
		date: '<?php echo sprintf($GLOBALS['TL_LANG']['ERR']['date'], $objDate->getInputFormat($GLOBALS['TL_CONFIG']['dateFormat'])); ?>',
		datim: '<?php echo sprintf($GLOBALS['TL_LANG']['ERR']['date'], $objDate->getInputFormat($GLOBALS['TL_CONFIG']['datimFormat'])); ?> ',
		time: '<?php echo sprintf($GLOBALS['TL_LANG']['ERR']['date'], $objDate->getInputFormat($GLOBALS['TL_CONFIG']['timeFormat'])); ?>'
	},
	regexp : {
		date: '<?php echo $strDateRegex; ?>',
		datim: '<?php echo $strDatimRegex ?>',
		time: '<?php echo $strTimeRegex ?>',
		phone: '/^[\d \+\(\)\/-]*$/'
	}
};
<?php foreach($GLOBALS['FORMCHECK'] as $formId): ?>
new FormCheck('<?php echo $formId?>',formcheckConfig);
<?php endforeach;?>
});
//--><!]]>
</script>
			
			