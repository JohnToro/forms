<?php
/**
*   Home page for the Forms plugin.
*   Used to either display a specific form, or to save the user-entered data.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2010 Lee Garner <lee@leegarner.com>
*   @package    forms
*   @version    0.1.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

require_once '../lib-common.php';
if (!in_array('forms', $_PLUGINS)) {
    COM_404();
}

USES_forms_functions();
USES_forms_class_form();

$action = '';
$actionval = '';
$expected = array(
    'savedata', 'results', 'mode', 'print', 'showform',
);
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
        $actionval = $_POST[$provided];
        break;
    } elseif (isset($_GET[$provided])) {
        $action = $provided;
        $actionval = $_GET[$provided];
        break;
    }
}

if (empty($action)) {
    $action = 'showform';
    COM_setArgNames(array('frm_id'));
    $frm_id = COM_getArgument('frm_id');
} else {
    $frm_id = isset($_REQUEST['frm_id']) ? $_REQUEST['frm_id'] : '';
}

if ($action == 'mode') $action = $actionval;

switch ($action) {
case 'savedata':
    $F = new frmForm($_POST['frm_id']);
    $redirect = str_replace('{site_url}', $_CONF['site_url'], $F->redirect);
    $errmsg = $F->SaveData($_POST);
    if (empty($errmsg)) {
        // Success
        if ($F->onsubmit & FRM_ACTION_DISPLAY) {
            $redirect = FRM_PI_URL . '/index.php?results=x&res_id=' .
                    $F->res_id;
            if ($F->onsubmit & FRM_ACTION_STORE) {
                $redirect .= '&token=' . $F->Result->Token();
            }
        } elseif (empty($redirect)) {
            /*if (isset($_POST['referrer']) && !empty($_POST['referrer'])) {
                $redirect = $_POST['referrer'];
            } else {
                $redirect = $_SERVER['HTTP_REFERER'];
            }*/
            $redirect = $_CONF['site_url'];
        }
        $u = parse_url($redirect);
        if ($F->submit_msg != '') {
            LGLIB_storeMessage($F->submit_msg);
        } else {
            $msg = isset($_POST['submit_msg']) ? $_POST['submit_msg'] : '1';
        }
        $q = array();
        if (!empty($u['query'])) {
            parse_str($u['query'], $q);
        }
        $q['msg'] = $msg;
        $q['plugin'] = $_CONF_FRM['pi_name'];
        $q['frm_id'] = $F->id;
        $redirect = $u['scheme'].'://'.$u['host'].$u['path'].'?';
        $q_arr = array();
        foreach($q as $key=>$value) {
            $q_arr[] = "$key=" . urlencode($value);
        }
        $redirect .= join('&', $q_arr);
            echo COM_refresh($redirect);
    } else {
        $msg = '2';
        if (!isset($_POST['referrer']) || empty($_POST['referrer'])) {
            $_POST['referrer'] = $_SERVER['HTTP_REFERER'];
        }
        $_POST['forms_error_msg'] = $errmsg;
        FRM_showForm($_POST['frm_id']);
    }
    exit;
    break;

case 'results':
    $res_id = isset($_REQUEST['res_id']) ? (int)$_REQUEST['res_id'] : 0;
    $frm_id = isset($_REQUEST['frm_id']) ? $_REQUEST['frm_id'] : '';
    $token  = isset($_GET['token']) ? $_GET['token'] : '';
    echo COM_siteHeader();
    if ($res_id > 0 && $frm_id != '') {
        $F = new frmForm($frm_id);
        $F->ReadData($res_id);
        if (($F->Result->uid == $_USER['uid'] && $F->Result->Token() == $token)
                || SEC_hasRights('forms.admin')) {
            $content .= '<h1>';
            $content .= $F->submit_msg == '' ? $LANG_FORMS['def_submit_msg'] : 
                    $F->submit_msg;
            $content .= '</h1>';
            $content .= $F->Prt($res_id);
            $content .= '<hr />' . LB;
            $content .= '<center><a href="' . FRM_PI_URL . 
                '/index.php?print=x&res_id=' . $res_id . '&frm_id=' . $frm_id .
                '" target="_blank">' .
                '<img src="' . $_CONF['layout_url'] . 
                '/images/print.png" border="0" title="' . 
                $LANG01[65] . '"></a></center>';
        }
    }
    echo $content;
    echo COM_siteFooter();
    exit;
    break;

case 'print':
    $res_id = isset($_REQUEST['res_id']) ? (int)$_REQUEST['res_id'] : 0;
    $frm_id = isset($_GET['frm_id']) ? $_GET['frm_id'] : '';
    if ($res_id > 0) {
        if (empty($frm_id)) {
            USES_forms_class_result();
            $R = new frmResult($res_id);
            if ($R->uid == $_USER['uid'] || SEC_hasRights('forms.admin')) {
                $content .= $R->Prt();
            }
        } else {
            $F = new frmForm($frm_id);
            $content .= $F->Prt($res_id);
        }
        echo $content;
        exit;
    }
    break;

case 'showform':
default:
    if ($frm_id == '') {
        // Missing form ID, we don't know what to do.
        echo COM_refresh($_CONF['site_url']);
        exit;
    } else {
        echo FRM_showForm($frm_id);
    }
    break;
}


/**
*   Display a form
*
*   @param  integer $frm_id     Form ID
*   @return string              HTML for the displayed form
*/
function FRM_showForm($frm_id)
{
    global $_CONF_FRM, $_CONF;

    // Instantiate the form and make sure the current user has access
    // to fill it out
    $F = new frmForm($frm_id, FRM_ACCESS_FILL);

    echo FRM_siteHeader($F->name);
    if (isset($_GET['msg']) && !empty($_GET['msg'])) {
        echo COM_showMessage(
                COM_applyFilter($_GET['msg'], true), $_CONF_FRM['pi_name']);
    }
    if ($F->id != '' && $F->access && $F->enabled) {
        echo $F->Render();
    } else {
        $msg = $F->noaccess_msg;
        if (!empty($msg)) {
            echo $msg;
        } else {
            echo COM_refresh($_CONF['site_url']);
        }
    }
    echo FRM_siteFooter();
}


?>
