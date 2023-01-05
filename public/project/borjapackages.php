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
 *    \file       htdocs/public/project/borjapackages.php
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
        print '<a href="https://borjabulgaria.com" target="_blank"><img id="dolpaymentlogo" src="' . $urllogo . '" ></a>';

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
			$thirdparty->country_id = 59;
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
		$proj->title = $langs->trans('OnlineFormPackageRequest');
		$proj->description = GETPOST('description', 'alphanohtml');
		$proj->opp_status = $defaultoppstatus;
		$proj->fk_opp_status = $defaultoppstatus;

		// Fill array 'array_options' with data from the form
		$extrafields->fetch_name_optionals_label($proj->table_element);
		$ret = $extrafields->setOptionalsFromPost((array)null, $proj);
		if ($ret < 0) {
			$error++;
		}

		//$proj->array_options['options_packageroofsys'] = !empty(GETPOST('package', 'aZ09')) ? GETPOST('package', 'aZ09') : $_POST['package'];
		$proj->array_options['options_packageroofsys'] = GETPOST('package', 'aZ09');

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

print '<h2 style="line-height:normal; text-align:center; font-size:28px;"><span style="letter-spacing:normal;"><span>Ценови Пакети за Покривни Системи<br>
<span style="font-size:16px;"><span><span style="color:#A0A09F;">Сезон 2022</span></span></span></span></span></h2>';

print '<div class="center">';

?>
    <div class="center">
        <div class="row-4">
            <div class="column-4">
                <div class="column-content-4">
                    <div class="head-lead-1">
                        <div class="head-lead-img">
                            <img src="./img/Roof-Carpenter.jpg"
                                 alt="Roof-Carpenter.jpg">
                        </div>
                        <p style="line-height:normal; text-align:center; font-size:14px;"><span
                                    style="letter-spacing:0.25em;border: 1px solid #333;padding: 10px 20px;"><span>Economy</span></span>
                        </p>
                        <p style="line-height:normal; text-align:center;"><span style="font-size:26px; color:#000000;">от 45 лв./кв.м.</span>
                        </p>
                        <div class="hide-lead-1">
                            <p style="line-height:normal; text-align:center; font-size:14px;">
                                    <span style="letter-spacing:0.25em;padding: 10px 20px;background-color: #333;">
                                        <span style="color: #fff;cursor: pointer;" onclick="openForm('Economy')">
                                            Избери Пакет
                                        </span>
                                    </span>
                            </p>
                        </div>
                    </div>
                    <div class="head-lead-img2">
                        <img src="./img/ECONOMY.jpg"
                             alt="ECONOMY.jpg">
                    </div>
                    <ul class="left" style="font-size:14px;">
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Керамични керемида серия Borja Class, BorjaTech&nbsp;</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Вентилирана покривна система</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;">
                                <span>Керамични аксесоари за покривна система</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Некерамични аксесоари </span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Сертификат за водонепроницаемост</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Допълнително укрепване на капаците</span>
                            </p>
                        </li>
                    </ul>
                </div>

                <div class="column-content-4">
                    <div class="head-lead-1">
                        <div class="head-lead-img">
                            <img src="./img/51253768251_301cf21254_c.jpg"
                                 alt="51253768251_301cf21254_c.jpg">
                        </div>
                        <p style="line-height:normal; text-align:center; font-size:14px;"><span
                                    style="letter-spacing:0.25em;border: 1px solid #333;padding: 10px 20px;"><span>Premium</span></span>
                        </p>
                        <p style="font-size:26px; line-height:normal; text-align:center;"><span
                                    style="font-size:26px;"><span><span
                                            style="letter-spacing:normal;"><span
                                                style="color:#000000;">от 65 лв./кв.м.</span></span></span></span>
                        </p>
                        <div class="hide-lead-1">
                            <p style="line-height:normal; text-align:center; font-size:14px;">
                                    <span style="letter-spacing:0.25em;padding: 10px 20px;background-color: #333;">
                                        <span style="color: #fff;cursor: pointer;" onclick="openForm('Premium')">
                                            Избери Пакет
                                        </span>
                                    </span>
                            </p>
                        </div>
                    </div>
                    <div class="head-lead-img2">
                        <img src="./img/PREMIUM.jpg"
                             alt="PREMIUM.jpg">
                    </div>
                    <ul class="left" style="font-size:14px;">
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span
                                        class="wixGuard">​</span><span>Керамични</span><span>&nbsp;керемид</span><span>а серия Borja Class, BorjaTech&nbsp;</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Вентилирана покривна система</span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;">
                                <span>Керамични аксесоари за покривна система</span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Некерамични аксесоари </span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Сертификат за водонепроницаемост</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Допълнително укрепване на капаците</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Допълнително подсилена аеродуфизна мебрана&nbsp; издържаща&nbsp; на -40 градуса</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Система за снегозадържане</span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Скоби за допълнителна сигурност при силен вятър</span>
                            </p>
                        </li>
                    </ul>
                </div>

                <div class="column-content-4">
                    <div class="head-lead-1">
                        <div class="head-lead-img">
                            <img src="./img/48664495236_a990e1e9ed_o.jpg"
                                 alt="48664495236_a990e1e9ed_o.jpg">
                        </div>
                        <p style="line-height:normal; text-align:center; font-size:14px;"><span
                                    style="letter-spacing:0.25em;border: 1px solid #333;padding: 10px 20px;"><span>PremiumJET</span></span>
                        </p>
                        <p style="font-size:26px; line-height:normal; text-align:center;"><span
                                    style="font-size:26px;"><span><span
                                            style="letter-spacing:normal;"><span
                                                style="color:#000000;">от 95 лв./кв.м.</span></span></span></span>
                        </p>
                        <div class="hide-lead-1">
                            <p style="line-height:normal; text-align:center; font-size:14px;">
                                    <span style="letter-spacing:0.25em;padding: 10px 20px;background-color: #333;">
                                        <span style="color: #fff;cursor: pointer;" onclick="openForm('PremiumJET')">
                                            Избери Пакет
                                        </span>
                                    </span>
                            </p>
                        </div>
                    </div>
                    <div class="head-lead-img2">
                        <img src="./img/JET.jpg"
                             alt="JET.jpg">
                    </div>
                    <ul class="left" style="font-size:14px;">
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;">
                                <span>Керемиди с BorjaJET принт технология</span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Вентилирана покривна система</span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;">
                                <span>Керамични аксесоари за покривна система</span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Некерамични аксесоари </span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Сертификат за водонепроницаемост</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Допълнително укрепване на капаците</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Допълнително подсилена аеродуфизна мебрана издържаща на -40 градуса</span>
                            </p>
                        </li>
                        <li>
                            <p style="line-height:2em; font-size:14px;"><span>Система за снегозадържане</span></p>
                        </li>
                        <li>
                            <p style="line-height:2em; font-size:14px;"><span>Скоби за допълнителна сигурност при силен вятър</span>
                            </p>
                        </li>
                    </ul>
                </div>

                <div class="column-content-4">
                    <div class="head-lead-1">
                        <div class="head-lead-img">
                            <img src="./img/51859494723_490b860fb2_c.jpg"
                                 alt="51859494723_490b860fb2_c.jpg">
                        </div>
                        <p style="line-height:normal; text-align:center; font-size:14px;"><span
                                    style="letter-spacing:0.25em;border: 1px solid #333;padding: 10px 20px;"><span>Ultra Premium</span></span>
                        </p>
                        <p style="font-size:26px; line-height:normal; text-align:center;"><span
                                    style="font-size:26px;"><span><span
                                            style="letter-spacing:normal;"><span
                                                style="color:#000000;">от 165 лв./кв.м.</span></span></span></span>
                        </p>
                        <div class="hide-lead-1">
                            <p style="line-height:normal; text-align:center; font-size:14px;">
                                    <span style="letter-spacing:0.25em;padding: 10px 20px;background-color: #333;">
                                        <span style="color: #fff;cursor: pointer;" onclick="openForm('UltraPremium')">
                                            Избери Пакет
                                        </span>
                                    </span>
                            </p>
                        </div>
                    </div>
                    <div class="head-lead-img2">
                        <img src="./img/ULTRA.jpg"
                             alt="ULTRA.jpg">
                    </div>
                    <ul class="left" style="font-size:14px;">
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Керамични керемида серия Borja Class, BorjaTech, BorjaJET, BorjaEXTREME</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Топлоизолационна покривна система от полиуретанови панели λ = 0,022</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Оптимална хидроизолация на покрива</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Вентилирана покривна система</span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;">
                                <span>Керамични аксесоари за покривна система</span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Некерамични аксесаори</span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Допълнително укрепване на капаците</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Система за снегозадържане</span></p>
                        </li>
                        <li>
                            <p style="line-height:2em; font-size:14px;">
                                <span>Оптимална хидроизолация на покрива&nbsp; </span></p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Алуминиеви вентилиращи летви&nbsp;</span>
                            </p>
                        </li>
                        <li style="line-height:2em;">
                            <p style="line-height:2em; font-size:14px;"><span>Изравнена покривна конструкция</span></p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="gap"></div>

        <div class="row-4">
            <div class="column-4">
                <div class="column-content-4" style="border: none;">
                    <p style="font-size:26px; line-height:normal; text-align:center;"><span><span><span
                                        style="letter-spacing:0.05em;"><span style="color:#282626;"><span
                                                style="font-size:26px;">Borja</span></span><span style="color:#E21C21;"><span
                                                style="font-size:26px;">TECH</span></span></span></span></span></p>
                    <img src="./img/ico-borjatech.png" alt="ico-borjatech.png"
                         style="width: 70px; height: 70px; object-fit: cover; object-position: 50% 50%;">
                    <p style="font-size:14px; line-height:1.5em; text-align:center;"><span
                                style="color:#282626;"><span><span
                                        style="font-size:14px;"><span>Plaster moulds and H-cassette production. More compact clay. Very low absortion and extremely strong.</span></span></span></span>
                    </p>
                </div>

                <div class="column-content-4" style="border: none;">
                    <p style="font-size:26px; text-align:center;"><span><span><span
                                        style="letter-spacing:0.05em;"><span style="color:#282626;"><span
                                                style="font-size:26px;">Borja</span></span><span style="color:#E21C21;"><span
                                                style="font-size:26px;">JET</span></span></span></span></span></p>
                    <img src="./img/ico-borjajet.png" alt="ico-borjatech.png"
                         style="width: 70px; height: 70px; object-fit: cover; object-position: 50% 50%;">
                    <p style="font-size:14px; line-height:1.5em; text-align:center;"><span
                                style="color:#282626;"><span><span
                                        style="font-size:14px;"><span>New Inkjet technology, the digital printing system allows us to create finishes never used before in the sector.</span></span></span></span>
                    </p>
                </div>

                <div class="column-content-4" style="border: none;">
                    <p style="font-size:26px; text-align:center;"><span><span><span
                                        style="letter-spacing:0.05em;"><span style="color:#282626;"><span
                                                style="font-size:26px;">Borja</span></span><span style="color:#E21C21;"><span
                                                style="font-size:26px;">EXTREME</span></span></span></span></span></p>
                    <img src="./img/ico-borjaextrem.png" alt="ico-borjatech.png"
                         style="width: 70px; height: 70px; object-fit: cover; object-position: 50% 50%;">
                    <p style="font-size:14px; line-height:1.5em; text-align:center;"><span
                                style="color:#282626;"><span><span
                                        style="font-size:14px;"><span>Exclusive to Tejas Borja, a roller kiln manufacturing process using ceramic clay with very low water absortion, less than 3%, to produce large tiles.</span></span></span></span>
                    </p>
                </div>

                <div class="column-content-4" style="border: none;">
                    <p style="font-size:26px; text-align:center;"><span><span><span
                                        style="letter-spacing:0.05em;"><span style="color:#282626;"><span
                                                style="font-size:26px;">Borja</span></span><span style="color:#E21C21;"><span
                                                style="font-size:26px;">CLASS</span></span></span></span></span></p>
                    <img src="./img/Borja-Class-150x150.png" alt="ico-borjatech.png"
                         style="width: 70px; height: 70px; object-fit: cover; object-position: 50% 50%;">
                    <p style="font-size:14px; line-height:1.5em; text-align:center;"><span
                                style="color:#282626;"><span><span
                                        style="font-size:14px;"><span>Manufacturing using high-quality, very fine, red sintered clay. very low absortion and extremely strong.</span></span></span></span>
                    </p>
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
				print '<span><img src="' . $imagetmp . '" alt="' . $imagetmp . '" /></span>';
				//print '</div>';
			}
			?>
        </div>

        <div class="gap"></div>

        <div class="contact_section">
            <p style="line-height:normal; text-align:center; font-size:16px;">
            <span style="letter-spacing:normal;">
                <span>Имате въпроси? Свържете се с нас на тел. <a class="href-phone" href="tel:+359885555070"
                                                                  aria-disabled="false"><span>+359 885 555 070</span></a></span>
            </span>
            </p>
        </div>

        <div class="gap"></div>

        <div class="carousel-view">
			<?php
			$dir = './img/slide2/';
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

print '<div id="divsubscribe" class="form-popup">';

dol_htmloutput_errors($errmsg);

// Print form
print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST" name="newlead">' . "\n";
print '<input type="hidden" name="token" value="' . newToken() . '" / >';
print '<input type="hidden" name="entity" value="' . $entity . '" />';
print '<input type="hidden" name="action" value="add" />';

//print $langs->trans('MailToSendSupplierRequestForQuotation');
print load_fiche_titre($langs->trans('MailToSendSupplierRequestForQuotation'), '', '');
//print '<br><br>';

print '<input type="hidden" name="package" id="package" value="" />';

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

print '<table id="tablesubscribe">' . "\n";

print '<tr>';
print '<td class="left">' . $langs->trans('Package') . ':</td>';
print '<td class="left"><span id="package2"></span></td>';
print '</tr>' . "\n";

// Firstname
print '<tr><td class="left">' . $langs->trans('Firstname') . ' <span style="color: red">*</span></td><td class="left"><input type="text" name="firstname" class="minwidth150" value="' . dol_escape_htmltag(GETPOST('firstname')) . '" required></td></tr>' . "\n";
// Lastname
print '<tr><td class="left">' . $langs->trans('Lastname') . ' <span style="color: red">*</span></td><td class="left"><input type="text" name="lastname" class="minwidth150" value="' . dol_escape_htmltag(GETPOST('lastname')) . '" required></td></tr>' . "\n";
// EMail
print '<tr><td class="left">' . $langs->trans('Email') . ' <span style="color: red">*</span></td><td class="left"><input type="text" name="email" maxlength="255" class="minwidth150" value="' . dol_escape_htmltag(GETPOST('email')) . '" required></td></tr>' . "\n";
// Phone
print '<tr><td class="left">' . $langs->trans('Phone') . ' <span style="color: red">*</span></td><td class="left"><input type="text" name="phone" maxlength="255" class="minwidth150" value="' . dol_escape_htmltag(GETPOST('phone')) . '" required></td></tr>' . "\n";

/*
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
*/

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

print '<button class="button" onclick="closeForm()">' . $langs->trans('Close') . '</button>';

print '</div>';

print "</form>\n";
?>
    <div class="mobile-hide">
        <p class="left" style="line-height:normal;"><span>Попълнете формата за контакт и потвърдете Вашата заявка за да получите:</span>
        </p>
        <ul class="left">
            <li style="line-height:normal;">
                <p style="line-height:normal;"><span>Персонализирана оферта</span></p>
            </li>
            <li style="line-height:normal;">
                <p style="line-height:normal;"><span">Изчисления по чертеж</span></p>
            </li>
            <li>
                <p style="line-height:normal;"><span">Актуална ценова листа</span></p>
            </li>
            <li style="line-height:normal;">
                <p style="line-height:normal;"><span>Безплатна консултация 30 мин.</span></p>
            </li>
        </ul>
    </div>
<?php
print '</div>';

print '</div>';

?>
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
    <script>
        function openForm(msg) {
            $("#package").val(msg);
            $("#package2").html(msg);
            document.getElementById("divsubscribe").style.display = "block";
        }

        function closeForm() {
            document.getElementById("divsubscribe").style.display = "none";
        }

    </script>

<?php

llxFooterVierge();

$db->close();
