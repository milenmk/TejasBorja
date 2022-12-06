<?php
/* Copyright (C) 2001-2002  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2001-2002  Jean-Louis Bergamo      <jlb@j1b.org>
 * Copyright (C) 2006-2013  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2012       Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2012       J. Fernando Lagrange    <fernando@demo-tic.org>
 * Copyright (C) 2018-2021  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2018       Alexandre Spangaro      <aspangaro@open-dsi.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *    \file       htdocs/public/project/borjajetoffer.php
 *    \ingroup    project
 *    \brief      Page to record a message/lead into a project/lead
 */

if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1); // This means this output page does not require to be logged.
}
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', 1); // We accept to go on this page from external website.
}
if (!defined('NOIPCHECK')) {
	define('NOIPCHECK', '1'); // Do not check IP defined into conf $dolibarr_main_restrict_ip
}
if (!defined('NOBROWSERNOTIF')) {
	define('NOBROWSERNOTIF', '1');
}
if (!defined('NOIPCHECK')) {
	define('NOIPCHECK', '1'); // Do not check IP defined into conf $dolibarr_main_restrict_ip
}

// For MultiCompany module.
// Do not use GETPOST here, function is not defined and define must be done before including main.inc.php
$entity = (!empty($_GET['entity']) ? (int)$_GET['entity'] : (!empty($_POST['entity']) ? (int)$_POST['entity'] : 1));
if (is_numeric($entity)) {
	define('DOLENTITY', $entity);
}

global $conf, $langs, $db, $user;

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/json.lib.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
dol_include_once('/custom/bthcommon/class/bthcommon.class.php');

// Init vars
$errmsg = '';
$error = 0;
$backtopage = GETPOST('backtopage', 'alpha');
$action = GETPOST('action', 'aZ09');

// Load translation files
$langs->loadLangs(['members', 'companies', 'install', 'other', 'projects', 'admin', 'mails', 'bthcommon@bthcommon']);

if (empty($conf->global->PROJECT_ENABLE_PUBLIC)) {
	print $langs->trans('Form for public lead registration has not been enabled');
	exit;
}

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(['publicnewleadcard', 'globalcard']);

$extrafields = new ExtraFields($db);

$object = new Project($db);

$bthc = new bthcommon($db);

$user->loadDefaultValues();

// Security check
if (empty($conf->project->enabled)) {
	accessforbidden('', 0, 0, 1);
}

/**
 * Show header for new member
 *
 * @param string $title       Title
 * @param string $head        Head array
 * @param int    $disablejs   More content into html header
 * @param int    $disablehead More content into html header
 * @param array  $arrayofjs   Array of complementary js files
 * @param array  $arrayofcss  Array of complementary css files
 *
 * @return    void
 */
function llxHeaderVierge($title, $head = '', $disablejs = 0, $disablehead = 0, $arrayofjs = '', $arrayofcss = '')
{

	global $conf;

	top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss); // Show html headers

	print '<body id="mainbody" class="publicnewmemberform">';

	// Define urllogo
	$urllogo = './img/tejas-borja.png';

	print '<div class="center">';

	// Output html code for logo
	if ($urllogo) {
		print '<div class="header">';
		print '<img id="dolpaymentlogo" src="' . $urllogo . '" >';

		print '<div class="header-menu">';
		print '<a class="mobile-hide" href="https://www.borjabulgaria.com">Borja Bulgaria</a>';
		print '<a href="https://www.borjabulgaria.com/borjatherm">BorjaTHERM</a>';
		print '<a href="https://www.borjabulgaria.com/produkti">BorjaClass</a>';
		print '<a href="https://www.borjabulgaria.com/borjajet">BorjaJET</a>';
		print '<a href="https://www.borjabulgaria.com/borjatech">BorjaTECH</a>';
		print '<div class="header-icons">';
		print '<a href="https://www.facebook.com/BorjaBulgaria/" target="_blank"><span class="icon facebook"></span></a>';
		print '<a href="https://www.youtube.com/user/TejasBorja" target="_blank"><span class="icon youtube"></span></a>';
		print '<a href="https://instagram.com/borjabulgaria" target="_blank"><span class="icon instagram"></span></a>';
		print '</div>';
		print '</div>';

		print '</div>';
	}

	if (!empty($conf->global->PROJECT_IMAGE_PUBLIC_NEWLEAD)) {
		print '<div class="backimagepublicnewlead">';
		print '<img id="idPROJECT_IMAGE_PUBLIC_NEWLEAD" src="' . $conf->global->PROJECT_IMAGE_PUBLIC_NEWLEAD . '">';
		print '</div>';
	}

	print '</div>';

	print '<div class="divmainbodylarge">';
}

/**
 * Show footer for new member
 *
 * @return    void
 */
function llxFooterVierge()
{

	print '</div>';

	printCommonFooter('public');

	print '<div class="foot">';
	print '<p style="text-align:center; line-height:normal; font-size:14px;"><span style="letter-spacing:normal;">© 2022 by Borja Bulgaria EOOD</span></p>';
	print '</div>';

	print "</body>\n";
	print "</html>\n";
}

/*
 * Actions
 */

$parameters = [];
// Note that $action and $object may have been modified by some hooks
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

// Action called when page is submitted
if (empty($reshook) && $action == 'add') {

	$error = 0;
	$urlback = '';

	$db->begin();

	if (!GETPOST('lastname')) {
		$error++;
		$errmsg .= $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Lastname')) . "<br>\n";
	}
	if (!GETPOST('firstname')) {
		$error++;
		$errmsg .= $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Firstname')) . "<br>\n";
	}
	if (!GETPOST('email')) {
		$error++;
		$errmsg .= $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Email')) . "<br>\n";
	}
	if (!GETPOST('description')) {
		$error++;
		$errmsg .= $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Message')) . "<br>\n";
	}
	if (GETPOST('email') && !isValidEmail(GETPOST('email'))) {
		$error++;
		$langs->load('errors');
		$errmsg .= $langs->trans('ErrorBadEMail', GETPOST('email')) . "<br>\n";
	}
	if (!GETPOST('phone')) {
		$error++;
		$errmsg .= $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Phone')) . "<br>\n";
	}
	// Set default opportunity status
	$defaultoppstatus = getDolGlobalString('PROJECT_DEFAULT_OPPORTUNITY_STATUS_FOR_ONLINE_LEAD');
	if (empty($defaultoppstatus)) {
		$error++;
		$langs->load('errors');
		$errmsg .= $langs->trans('ErrorModuleSetupNotComplete', $langs->transnoentitiesnoconv('Project')) . "<br>\n";
	}

	$proj = new Project($db);
	$thirdparty = new Societe($db);

	if (!$error) {
		// Search thirdparty and set it if found to the new created project
		$result = $thirdparty->fetch(0, '', '', '', '', '', '', '', '', '', GETPOST('email'));
		if ($result > 0) {
			$proj->socid = $thirdparty->id;
		} else {
			// Create the prospect
			if (GETPOST('societe')) {
				$thirdparty->name = GETPOST('societe');
				$thirdparty->name_alias = dolGetFirstLastname(GETPOST('firstname'), GETPOST('lastname'));
			} else {
				$thirdparty->name = dolGetFirstLastname(GETPOST('firstname'), GETPOST('lastname'));
			}
			$thirdparty->email = GETPOST('email');
			$thirdparty->phone = GETPOST('phone');
			$thirdparty->address = GETPOST('address');
			$thirdparty->zip = GETPOST('zip');
			$thirdparty->town = GETPOST('town');
			$thirdparty->country_id = GETPOST('country_id', 'int');
			$thirdparty->state_id = GETPOST('state_id');
			$thirdparty->client = $thirdparty::PROSPECT;
			$thirdparty->code_client = 'auto';
			$thirdparty->code_fournisseur = 'auto';

			// Fill array 'array_options' with data from the form
			$extrafields->fetch_name_optionals_label($thirdparty->table_element);
			$ret = $extrafields->setOptionalsFromPost((array)null, $thirdparty, '', 1);
			//var_dump($thirdparty->array_options);exit;
			if ($ret < 0) {
				$error++;
				$errmsg = ($extrafields->error ? $extrafields->error . '<br>' : '') . join('<br>', $extrafields->errors);
			}

			if (!$error) {
				$result = $thirdparty->create($user);
				if ($result <= 0) {
					$error++;
					$errmsg = ($thirdparty->error ? $thirdparty->error . '<br>' : '') . join('<br>', $thirdparty->errors);
				} else {
					$proj->socid = $thirdparty->id;
				}
			}

			$thirdparty->fetch($thirdparty->id);
			if (GETPOST('custcat', 'az09')) {
				$thirdparty->setCategories(GETPOST('custcat', 'az09'), 'customer');
			}
		}
	}

	if (!$error) {
		// Defined the ref into $defaultref
		$defaultref = '';
		$modele = empty($conf->global->PROJECT_ADDON) ? 'mod_project_simple' : $conf->global->PROJECT_ADDON;

		// Search template files
		$file = '';
		$classname = '';
		$filefound = 0;
		$dirmodels = array_merge(['/'], $conf->modules_parts['models']);
		foreach ($dirmodels as $reldir) {
			$file = dol_buildpath($reldir . 'core/modules/project/' . $modele . '.php', 0);
			if (file_exists($file)) {
				$filefound = 1;
				$classname = $modele;
				break;
			}
		}

		if ($filefound) {
			$result = dol_include_once($reldir . 'core/modules/project/' . $modele . '.php');
			$modProject = new $classname();

			$defaultref = $modProject->getNextValue($thirdparty, $object);
		}

		if (is_numeric($defaultref) && $defaultref <= 0) {
			$defaultref = '';
		}

		if (empty($defaultref)) {
			$defaultref = 'PJ' . dol_print_date(dol_now(), 'dayrfc');
		}

		$proj->ref = $defaultref;
		$proj->statut = $proj::STATUS_DRAFT;
		$proj->status = $proj::STATUS_DRAFT;
		$proj->email = GETPOST('email');
		$proj->public = 1;
		$proj->usage_opportunity = 1;
		$proj->title = $langs->trans('OnlineFormBorjaJetRequest');
		$proj->description = GETPOST('description', 'alphanohtml');
		$proj->opp_status = $defaultoppstatus;
		$proj->fk_opp_status = $defaultoppstatus;

		// Fill array 'array_options' with data from the form
		$extrafields->fetch_name_optionals_label($proj->table_element);
		$ret = $extrafields->setOptionalsFromPost((array)null, $proj);
		if ($ret < 0) {
			$error++;
		}

		// Create the project
		$result = $proj->create($user);
		if ($result > 0) {
			require_once DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php';
			$object = $proj;

			if (!empty($backtopage)) {
				$urlback = $backtopage;
			} elseif (!empty($conf->global->PROJECT_URL_REDIRECT_LEAD)) {
				$urlback = $conf->global->PROJECT_URL_REDIRECT_LEAD;
				// TODO Make replacement of __AMOUNT__, etc...
			} else {
				$urlback = $_SERVER['PHP_SELF'] . '?action=added&token=' . newToken();
			}

			if (!empty($entity)) {
				$urlback .= '&entity=' . $entity;
			}

			dol_syslog('project lead ' . $proj->ref . ' has been created, we redirect to ' . $urlback);
		} else {
			$error++;
			$errmsg .= $proj->error . '<br>' . join('<br>', $proj->errors);
		}
	}

	if (!$error) {
		$db->commit();

		Header('Location: ' . $urlback);
		exit;
	} else {
		$db->rollback();
	}
}

// Action called after a submitted was sent and member created successfully
// If MEMBER_URL_REDIRECT_SUBSCRIPTION is set to url we never go here because a redirect was done to this url.
// backtopage parameter with an url was set on member submit page, we never go here because a redirect was done to this url.
if (empty($reshook) && $action == 'added') {
	//llxHeaderVierge('Borja - твоят нов покрив');

	// Si on a pas ete redirige
	print '<br><br>';
	print '<div class="center">';
	print $langs->trans('NewLeadbyWebModif');
	print '</div>';

	//llxFooterVierge();
	echo '<script>setTimeout(function(){ window.location.href= "' . $_SERVER['PHP_SELF'] . '";}, 3000);</script>';
}

/*
 * View
 */

$form = new Form($db);
$formcompany = new FormCompany($db);

$extrafields->fetch_name_optionals_label($object->table_element); // fetch optionals attributes and labels

llxHeaderVierge('Borja - твоят нов покрив', '', '', '', '', ['public/project/css/main.css', 'public/project/css/slick.css', 'public/project/css/slick-theme.css']);

print load_fiche_titre('', '', '', 0, 0, 'center');

print '<h2 style="line-height:normal; text-align:center; font-size:28px;"><span style="letter-spacing:normal;"><span>Вашият Покрив от Borja Bulgaria<br>
<span style="font-size:16px;"><span><span style="color:#A0A09F;">Керемиди от ново поколение</span></span></span></span></span></h2>';

print '<div class="center">';

?>
    <div class="center">

        <img src="<?= DOL_URL_ROOT . '/public/project/img/jetintro.jpg' ?>" width="100%" height="auto" alt="JetIntro"/>

        <div class="gap"></div>

        <div class="row-2 jetintro">
            <div class="column-2">
                <div class="center" style="width: 60%;">
                    &nbsp;
                    <p>
                        Borja <span style="color:#E21C21;">JET</span> FLAT-10/FLAT-5XL™
                        <br><br>
                        Отлична дефиниция при възпроизвеждането на всички видове ефекти, които никога не са постигани преди това върху керемиди.
                        <br>
                        Това дава възможност за сливане на богатството на естествените материали с техническите качества на керамичните керемиди.
                    </p>
                </div>
            </div>
            <div class="column-2">
                <div class="row-2">
                    <div class="column-2"><img src="img/jetoffer/1.jpg" width="95%" height="auto"/></div>
                    <div class="column-2"><img src="img/jetoffer/2.jpg" width="95%" height="auto"/></div>

                    <div class="column-2"><img src="img/jetoffer/3.jpg" width="95%" height="auto"/></div>
                    <div class="column-2"><img src="img/jetoffer/4.jpg" width="95%" height="auto"/></div>

                    <div class="column-2"><img src="img/jetoffer/5.jpg" width="95%" height="auto"/></div>
                    <div class="column-2"><img src="img/jetoffer/6.jpg" width="95%" height="auto"/></div>
                </div>
            </div>
        </div>

        <div class="gap"></div>

        <div class="carousel-view">
			<?php
			$dir = './img/slide1/';
			$images_array = glob($dir . '*.jpg');
			foreach ($images_array as $imagetmp) {
				//$image = str_replace($dir, '', $imagetmp);
				//print '<div class="carouselbox">';
				print '<p style="border: 10px solid #fff;"><img src="' . $imagetmp . '" alt="' . $imagetmp . '" width="auto" height="280px" /></p>';
				//print '</div>';
			}
			?>
        </div>

        <div class="gap"></div>

    </div>
<?php

print '<div class="center center-form">';

dol_htmloutput_errors($errmsg);

// Print form
print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" name="newlead">' . "\n";
print '<input type="hidden" name="token" value="' . newToken() . '" / >';
print '<input type="hidden" name="entity" value="' . $entity . '" />';
print '<input type="hidden" name="action" value="add" />';

print '<h2 style="color:#E21C21;">Вашият Borja JET Покрив</h2>';

print '<h4>Попълнете формата за да получите актуални цени на нашите продукти</h4>';

print dol_get_fiche_head('');

/*
print '<script type="text/javascript">
jQuery(document).ready(function () {
    jQuery(document).ready(function () {
        jQuery("#selectcountry_id").change(function() {
           document.newlead.action.value="create";
           document.newlead.submit();
        });
    });
});
</script>';
*/

print '<table id="tablesubscribe" style="background: rgba(227,227,227,0.63);padding: 10px 10px;">' . "\n";

// Firstname
print '<tr><td class="left">' . $langs->trans('Firstname') . ' <span style="color: red">*</span></td><td class="left"><input type="text" name="firstname" class="minwidth150" value="' . dol_escape_htmltag(GETPOST('firstname')) . '" required></td></tr>' . "\n";
// Lastname
print '<tr><td class="left">' . $langs->trans('Lastname') . ' <span style="color: red">*</span></td><td class="left"><input type="text" name="lastname" class="minwidth150" value="' . dol_escape_htmltag(GETPOST('lastname')) . '" required></td></tr>' . "\n";
// EMail
print '<tr><td class="left">' . $langs->trans('Email') . ' <span style="color: red">*</span></td><td class="left"><input type="text" name="email" maxlength="255" class="minwidth150" value="' . dol_escape_htmltag(GETPOST('email')) . '" required></td></tr>' . "\n";
// Phone
print '<tr><td class="left">' . $langs->trans('Phone') . ' <span style="color: red">*</span></td><td class="left"><input type="text" name="phone" maxlength="255" class="minwidth150" value="' . dol_escape_htmltag(GETPOST('phone')) . '" required></td></tr>' . "\n";
// Company
print '<tr id="trcompany" class="trcompany"><td class="left">' . $langs->trans('Company') . '</td><td class="left"><input type="text" name="societe" class="minwidth150" value="' . dol_escape_htmltag(GETPOST('societe')) . '"></td></tr>' . "\n";
// Address
print '<tr><td class="left">' . $langs->trans('Address') . '</td><td class="left">' . "\n";
print '<textarea name="address" id="address" wrap="soft" class="quatrevingtpercent" rows="' . ROWS_2 . '">' . dol_escape_htmltag(GETPOST('address', 'restricthtml'), 0, 1) . '</textarea></td></tr>' . "\n";
// Zip / Town
print '<tr><td class="left">' . $langs->trans('Zip') . ' / ' . $langs->trans('Town') . '</td><td class="left">';
print $formcompany->select_ziptown(GETPOST('zipcode'), 'zipcode', ['town', 'selectcountry_id', 'state_id'], 6, 1);
print ' / ';
print $formcompany->select_ziptown(GETPOST('town'), 'town', ['zipcode', 'selectcountry_id', 'state_id'], 0, 1);
print '</td></tr>';
// Country
print '<tr><td class="left">' . $langs->trans('Country') . '</td><td class="left">';
$country_id = GETPOST('country_id');
if (!$country_id && !empty($conf->global->PROJECT_NEWFORM_FORCECOUNTRYCODE)) {
	$country_id = getCountry($conf->global->PROJECT_NEWFORM_FORCECOUNTRYCODE, 2, $db, $langs);
}
if (!$country_id && !empty($conf->geoipmaxmind->enabled)) {
	$country_code = dol_user_country();
	//print $country_code;
	if ($country_code) {
		$new_country_id = getCountry($country_code, 3, $db, $langs);
		//print 'xxx'.$country_code.' - '.$new_country_id;
		if ($new_country_id) {
			$country_id = $new_country_id;
		}
	}
}
$country_code = getCountry($country_id, 2, $db, $langs);
print $form->select_country('59', 'country_id', '', '', 'minwidth200', '', '', '', '', '', '');
print '</td></tr>';

//Customer category
print '<tr><td class="left">' . $langs->trans('CustomersCategorie') . '</td><td class="left">';
print '<select name="custcat" class="minwidth200">';
print '<option value=""></option>';
foreach ($bthc->getCustomerCategories() as $cat) {
	print '<option value="' . $cat['id'] . '">' . $cat['label'] . '</option>';
}
print '</select>';
print '</td></tr>';

// Other attributes
$tpl_context = 'public';                                          // define template context to public
//include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';
// Comments
print '<tr>';
print '<td class="tdtop left">' . $langs->trans('MailText') . ' <span style="color: red">*</span></td>';
print '<td class="tdtop left"><textarea name="description" id="description" wrap="soft" class="quatrevingtpercent" rows="' . ROWS_5 . '" required>' . dol_escape_htmltag(GETPOST('description', 'restricthtml'), 0, 1) . '</textarea></td>';
print '</tr>' . "\n";

print "</table>\n";

print '<span class="opacitymedium">' . $langs->trans('FieldsWithAreMandatory', '*') . '</span><br>';
//print $langs->trans("FieldsWithIsForPublic",'**').'<br>';

print dol_get_fiche_end();

// Save
print '<div class="center">';
print '<input type="submit" value="' . $langs->trans('Submit') . '" id="submitsave" class="button">';

print '</div>';

print "</form>\n";
print '</div>';

print '</div>';

?>
    <div class="gap"></div>

    <div class="contact_section">
        <p style="line-height:normal; text-align:center; font-size:16px;">
            <span style="letter-spacing:normal;">
                <span>Не желаете да чакате? Свържете се с нас на тел. <a class="href-phone" href="tel:+359885555070"
                                                                         aria-disabled="false"><span>+359 885 555 070</span></a></span>
            </span>
        </p>
    </div>

    <div class="gap"></div>

    <script type="text/javascript" src="./js/slick.js"></script>
    <script>
        jQuery('.carousel-view').slick({
            dots: false,
            infinite: false,
            speed: 300,
            slidesToShow: 6,
            slidesToScroll: 1,
            responsive: [
                {
                    breakpoint: 600,
                    settings: {
                        arrows: false,
                        slidesToShow: 2,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        arrows: false,
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });


    </script>

    <div class="row-3">
        <div class="column-3" style="background-color: rgb(0,0,0);">
            <div class="center">>
                <svg style="padding-top: 50px;" preserveAspectRatio="xMidYMid meet" data-bbox="28.4 38.8 143.2 122.4" viewBox="28.4 38.8 143.2 122.4" height="100" width="100"
                     xmlns="http://www.w3.org/2000/svg" data-type="color" aria-hidden="true">
                    <g>
                        <path d="M97.438 141.033H31.402a3.004 3.004 0 0 1-3.002-3.007V41.807a3.004 3.004 0 0 1 3.002-3.007h120.066a3.004 3.004 0 0 1 3.002 3.007V86.91h-6.003V44.814H34.403v90.206h63.035v6.013z"
                              fill="#FFFFFF"></path>
                        <path d="M106.443 62.855H73.425a3.004 3.004 0 0 1-3.002-3.007V41.807a3.004 3.004 0 0 1 3.002-3.007h33.018a3.004 3.004 0 0 1 3.002 3.007v18.041a3.004 3.004 0 0 1-3.002 3.007zm-30.017-6.014h27.015V44.814H76.426v12.027z"
                              fill="#FFFFFF"></path>
                        <path fill="#FFFFFF" d="M46.41 122.992v6.014h-6.003v-6.014h6.003z"></path>
                        <path fill="#FFFFFF" d="M58.416 122.992v6.014h-6.003v-6.014h6.003z"></path>
                        <path fill="#FFFFFF" d="M70.423 122.992v6.014H64.42v-6.014h6.003z"></path>
                        <path fill="#FFFFFF" d="M167.355 161.2l-18.009-18.041 4.244-4.252 18.01 18.041-4.245 4.252z"></path>
                        <path d="M130.506 153.012c-18.206 0-33.018-14.832-33.018-33.062 0-18.232 14.812-33.064 33.018-33.064s33.018 14.832 33.018 33.064c0 18.23-14.812 33.062-33.018 33.062zm0-60.112c-14.897 0-27.015 12.135-27.015 27.05 0 14.914 12.118 27.048 27.015 27.048s27.015-12.135 27.015-27.048c0-14.916-12.118-27.05-27.015-27.05z"
                              fill="#FFFFFF"></path>
                        <path fill="#FFFFFF" d="M118.449 107.958v24.055h-6.003v-24.055h6.003z"></path>
                        <path fill="#FFFFFF" d="M127.454 107.958v24.055h-6.003v-24.055h6.003z"></path>
                        <path fill="#FFFFFF" d="M139.461 107.958v24.055h-6.003v-24.055h6.003z"></path>
                        <path fill="#FFFFFF" d="M148.466 107.958v24.055h-6.003v-24.055h6.003z"></path>
                    </g>
                </svg>
                <h4 style="color: #FFFFFF;">ЦЕНОВА ЛИСТА</h4>
                <div class="center" style="color: #FFFFFF;width: 50%;padding-bottom: 50px;">Попълнете формата за да получите актуална ценова листа на Borja Bulgaria</div>
            </div>
        </div>
        <div class="column-3" style="background-color: rgb(96,94,94);">
            <div class="center">
                <svg style="padding-top: 50px;" preserveAspectRatio="xMidYMid meet" data-bbox="57.5 37.5 85 125.004" viewBox="57.5 37.5 85 125.004" height="100" width="100"
                     xmlns="http://www.w3.org/2000/svg" data-type="color" aria-hidden="true">
                    <g>
                        <path d="M142.413 148.448c-.015.07-.044.135-.063.203.019-.068.048-.133.063-.203z" fill="#FFFFFF"></path>
                        <path d="M142.169 149.179c-.038.083-.087.159-.13.239.043-.08.092-.156.13-.239z" fill="#FFFFFF"></path>
                        <path d="M141.157 150.515c.039-.032.066-.073.103-.107a3.71 3.71 0 0 1-.265.219c.053-.039.111-.07.162-.112z" fill="#FFFFFF"></path>
                        <path d="M141.592 150.067c.066-.076.132-.15.191-.231-.06.081-.126.155-.191.231z" fill="#FFFFFF"></path>
                        <path d="M112.265 81.8H87.736c-.632 0-1.144.511-1.144 1.142v10.684c0 .631.512 1.142 1.144 1.142h24.529c.632 0 1.144-.511 1.144-1.142V82.942c0-.63-.512-1.142-1.144-1.142z"
                              fill="#ce2026"></path>
                        <path d="M142.429 52.801c-.016-.081-.043-.158-.064-.237a3.737 3.737 0 0 0-.131-.421c-.035-.088-.078-.17-.119-.254a3.923 3.923 0 0 0-.19-.35 3.857 3.857 0 0 0-.611-.738 3.62 3.62 0 0 0-.31-.255c-.074-.055-.146-.111-.225-.161a3.733 3.733 0 0 0-.363-.197c-.082-.04-.16-.084-.245-.119a3.752 3.752 0 0 0-.437-.135c-.077-.02-.15-.049-.228-.064l-.029-.008-65.296-12.298a3.723 3.723 0 0 0-3.057.788 3.7 3.7 0 0 0-1.343 2.852v8.595h-8.57a3.707 3.707 0 0 0-3.711 3.704v94.162a3.708 3.708 0 0 0 3.711 3.704h54.807v9.349c0 .629.331 1.21.872 1.532.541.32 1.211.333 1.765.037l5.096-2.755 5.096 2.755a1.785 1.785 0 0 0 2.638-1.569v-9.349h7.305c.238 0 .468-.027.693-.07.086-.016.167-.044.25-.066.138-.036.275-.074.406-.126.092-.036.179-.08.267-.123.117-.057.231-.116.341-.185a3.926 3.926 0 0 0 .514-.391c.119-.106.227-.22.331-.341.065-.076.132-.15.191-.231.097-.132.177-.273.256-.418.043-.08.093-.156.13-.239a3.65 3.65 0 0 0 .181-.528c.019-.069.048-.133.063-.203.055-.253.087-.514.087-.784V53.502c0-.24-.027-.473-.071-.701zm-7.352 90.395v.764H64.923V57.206h70.154v85.99z"
                              fill="#FFFFFF"></path>
                    </g>
                </svg>
                <h4 style="color: #FFFFFF;">КАТАЛОГ</h4>
                <div class="center" style="color: #FFFFFF;width: 50%;padding-bottom: 50px;">Ще изпратим каталог с наличните цветови комбинации</div>
            </div>
        </div>
        <div class="column-3" style="background-color: rgb(237,237,237);">
            <div class="center">
                <svg style="padding-top: 50px;" preserveAspectRatio="xMidYMid meet" data-bbox="0.064 21.9 199.636 155.7" viewBox="0.064 21.9 199.636 155.7" height="100" width="100"
                     xmlns="http://www.w3.org/2000/svg" data-type="color" aria-hidden="true">
                    <g>
                        <path fill="#000000"
                              d="M6.4 170.2c.5.3 1.1.5 1.7.5.5 0 .9-.1 1.4-.3l39.3-17.2c.7-.3 1.2-.8 1.6-1.4l54.2-95.1c3.2-5.6 1.2-12.8-4.3-16L70.7 23.5c-2.7-1.6-5.9-2-9-1.2-3 .8-5.6 2.8-7.1 5.5l-54.1 95c-.4.6-.5 1.3-.4 2.1l4.7 42.7c.1 1.1.7 2 1.6 2.6zm14.4-38l50-87.6 11.3 6.5L32 138.8l-11.2-6.6zm-1.9-1.1l-11.4-6.7 49.9-87.6L69 43.5l-50.1 87.6zm24.4 17l-19.9 8.7-14-8.1-2.4-22 36.3 21.4zm2.1-1.4L34 140l50.1-87.7 11.3 6.6-50 87.8zM63.5 28.9c1.3-.3 2.6-.2 3.8.5l29.5 17.1c2.4 1.4 3.2 4.4 1.8 6.8L96.5 57 58.4 35l2-3.6c.7-1.3 1.8-2.2 3.1-2.5z"></path>
                        <path fill="#000000" d="M199.7 175.6v2H5.7v-2h194z"></path>
                    </g>
                </svg>
                <h4 style="color: #ce2026;">ИНДИВИДУАЛНА ОФЕРТА</h4>
                <div class="center" style="width: 50%;padding-bottom: 50px;">Възможност за количествено и стойностно изчисление на Вашия покрив</div>
            </div>
        </div>
    </div>
<?php

llxFooterVierge();

$db->close();
