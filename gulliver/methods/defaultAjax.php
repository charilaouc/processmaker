<?php

use ProcessMaker\Plugins\PluginRegistry;

if (isset($_SESSION['CURRENT_PAGE_INITILIZATION'])) {
    eval($_SESSION['CURRENT_PAGE_INITILIZATION']);
}

if (!defined('XMLFORM_AJAX_PATH')) {
    define('XMLFORM_AJAX_PATH', PATH_XMLFORM);
}

$_DBArray = array();
if (isset($_SESSION['_DBArray'])) {
    $_DBArray = $_SESSION['_DBArray'];
}

$xmlFile = G::getUIDName(urlDecode($_POST['form']));
$sPath = XMLFORM_AJAX_PATH;

//if the xmlform file doesn't exist, then try with the plugins folders
if (!is_file(XMLFORM_AJAX_PATH . $xmlFile)) {
    $aux = explode(PATH_SEP, $xmlFile);
    //check if G_PLUGIN_CLASS is defined, because publisher can be called without an environment
    if (count($aux) == 2 && defined('G_PLUGIN_CLASS')) {
        $oPluginRegistry = PluginRegistry::loadSingleton();
        if ($oPluginRegistry->isRegisteredFolder($aux[0])) {
            $sPath = PATH_PLUGINS;
        }
    }
}

$G_FORM = new Form($xmlFile, $sPath);
$G_FORM->id = urlDecode($_POST['form']);
$G_FORM->values = isset($_SESSION[$G_FORM->id]) ? $_SESSION[$G_FORM->id] : array();

$newValues = (Bootstrap::json_decode(urlDecode(stripslashes($_POST['fields']))));

if (isset($_POST['grid'])) {
    $_POST['row'] = (int) $_POST['row'];
    $aAux = array();

    foreach ($newValues as $sKey => $newValue) {
        $newValue = (array) $newValue;
        $aKeys = array_keys($newValue);
        if (count($aKeys) > 0) {
            $aValues = array();
            for ($i = 1; $i <= ($_POST['row'] - 1); $i++) {
                $aValues[$i] = array($aKeys[0] => '');
            }
            $aValues[$_POST['row']] = array($aKeys[0] => $newValue[$aKeys[0]]);
            $newValues[$sKey]->{$_POST['grid']} = $aValues;
            unset($newValues[$sKey]->{$aKeys[0]});
        }
    }
}

//Next Lines re-build newValues array to send multiple dependent fields merged by row into a grid.
if (count($newValues) > 1 && isset($_POST['grid'])) {
    $fieldBase = array();
    foreach ($newValues as $key => $values) {
        for ($r2 = 1; $r2 <= $_POST['row']; $r2++) {
            foreach ($values as $class => $value) {
                if ($class == $_POST['grid']) {
                    $value = (array) $value;
                    $arrayK = $value[$r2];
                    foreach ($arrayK as $key2 => $val) {
                        $fieldBase[$r2][$key2] = is_array($val) ? $val[$key2] : $val;
                    }
                }
            }
        }
    }
    $newValues[0]->{$_POST['grid']} = $fieldBase;
}

//Resolve dependencies
//Returns an array ($dependentFields) with the names of the fields
//that depends of fields passed through AJAX ($_GET/$_POST)
//Returns all dependencies of all fields, this in grids
$dependentFields = array();
$aux = array();
for ($r = 0; $r < count($newValues); $r++) {
    $newValues[$r] = (array) $newValues[$r];
    $G_FORM->setValues($newValues[$r]);
    //Search dependent fields
    foreach ($newValues[$r] as $k => $v) {
        if (!is_array($v)) {
            $myDependentFields = subDependencies($k, $G_FORM, $aux);
            $_SESSION[$G_FORM->id][$k] = $v;
        } else {
            foreach ($v[$_POST['row']] as $k1 => $v1) {
                $myDependentFields = subDependencies($k1, $G_FORM, $aux, $_POST['grid']);
                $_SESSION[$G_FORM->id][$_POST['grid']] = [
                    $_POST['row'] => [
                        $k1 => $v1
                    ]
                ];
                $G_FORM->values[$_POST['grid']][$_POST['row']][$k1] = $v1;
            }
        }
        $dependentFields = array_merge($dependentFields, $myDependentFields);
    }
}

$dependentFields = array_unique($dependentFields);

//Update when is depenfield set empty
$newForm = $G_FORM->values;
foreach ($newForm as $fKey => $values) {
    foreach ($dependentFields as $att) {
        if ($att == $fKey) {
            $newForm[$fKey] = '';
        }
    }
}
$G_FORM->values = $newForm;

//Delete all dependencies of all fields, we're interested only in the fields sending from AJAX, this in grids
$arrayFieldSubDependent = array();

if (isset($_POST["grid"])) {
    $arrayField = (array) (Bootstrap::json_decode(urlDecode(stripslashes($_POST["fields"]))));
    $arrayDependentField = array();
    $ereg = null;

    foreach ($arrayField as $fieldData) {
        $arrayAux = (array) ($fieldData);

        foreach ($arrayAux as $index => $value) {
            $ereg = $ereg . (($ereg != null) ? "|" : null) . $index; //Concatenate field
        }
    }

    if ($ereg != null) {
        foreach ($dependentFields as $value) {
            //Direct dependent fields
            if (preg_match("/^(?:$ereg)\|[^\|]*$/", $value)) {
                $arrayAux = explode("|", $value);

                $arrayDependentField[] = $arrayAux[1];
            }

            //Subdependent fields
            if (preg_match("/^(?:$ereg)\|.*$/", $value)) {
                $arrayAux = explode("|", $value);
                $index = $arrayAux[0];

                unset($arrayAux[0]);

                if (isset($arrayFieldSubDependent[$index])) {
                    $arrayFieldSubDependent[$index] = array_unique(array_merge($arrayFieldSubDependent[$index], $arrayAux));
                } else {
                    $arrayFieldSubDependent[$index] = array_unique($arrayAux);
                }
            }
        }
    }

    $dependentFields = array_unique($arrayDependentField);
}

//Completed all fields of the grid
if (isset($_POST["grid"]) && isset($_POST["gridField"])) {
    //Completed all fields of the grid
    $arrayGridField = (array) (Bootstrap::json_decode(urldecode(stripslashes($_POST["gridField"]))));

    foreach ($arrayGridField as $index => $value) {
        $G_FORM->values[$_POST["grid"]][$_POST["row"]][$index] = $value;
    }

    //Delete all fields subdependent
    foreach ($arrayFieldSubDependent as $index1 => $value1) {
        $arrayAux = $value1;

        foreach ($arrayAux as $value2) {
            unset($G_FORM->values[$_POST["grid"]][$_POST["row"]][$value2]);
        }
    }
}

//Parse and update the new content
$newContent = $G_FORM->getFields(PATH_CORE . "templates" . PATH_SEP . "xmlform.html", (isset($_POST["row"]) ? $_POST["row"] : -1));

//Returns the dependentFields's content
$sendContent = array();
$r = 0;

//Set data
foreach ($dependentFields as $d) {
    $d = trim($d);
    $sendContent[$r] = new stdclass();
    $sendContent[$r]->name = $d;
    $sendContent[$r]->content = new stdclass();

    if (!isset($_POST['grid'])) {
        if (isset($G_FORM->fields[$d])) {
            foreach ($G_FORM->fields[$d] as $attribute => $value) {
                switch ($attribute) {
                    case 'type':
                        $sendContent[$r]->content->{$attribute} = $value;
                        break;
                    case 'options':
                        $sendContent[$r]->content->{$attribute} = toJSArray($value, $sendContent[$r]->content->type);
                        break;
                }
            }
            $sendContent[$r]->value = isset($G_FORM->values[$d]) ? $G_FORM->values[$d] : '';
        }
    } else {
        foreach ($G_FORM->fields[$_POST['grid']]->fields[$d] as $attribute => $value) {
            switch ($attribute) {
                case 'type':
                    $sendContent[$r]->content->{$attribute} = $value;
                    break;
                case 'options':
                    if ($sendContent[$r]->content->type != "text" && $sendContent[$r]->content->type != "textarea") {
                        $sendContent[$r]->content->{$attribute} = toJSArray($value);
                    } else {
                        $sendContent[$r]->content->{$attribute} = toJSArray((isset($value[$_POST["row"]]) ? array($value[$_POST["row"]]) : array()));
                    }
                    break;
            }
        }
        $sendContent[$r]->value = isset($G_FORM->values[$_POST['grid']][$_POST['row']][$d]) ? $G_FORM->values[$_POST['grid']][$_POST['row']][$d] : '';
    }

    $r = $r + 1;
}

echo Bootstrap::json_encode($sendContent);

function toJSArray($array, $type = '')
{
    $result = array();
    foreach ($array as $k => $v) {
        $o = new stdclass();
        $o->key = $k;
        // TODO: review the condition to make the differentiation to dependent dropdowns in a grid function.
        // this way of validation is if you have a dependent field in text fields
        $o->value = ($type == 'text' || $type == 'textarea') ? $k : $v;
        $result[] = $o;
    }
    return $result;
}

function subDependencies($k, &$G_FORM, &$aux, $grid = '')
{
    if (array_search($k, $aux) !== false) {
        return array();
    }
    if ($grid == '') {
        if (!array_key_exists($k, $G_FORM->fields)) {
            return array();
        }
        if (!isset($G_FORM->fields[$k]->dependentFields)) {
            return array();
        }
        $aux[] = $k;
        if (strpos($G_FORM->fields[$k]->dependentFields, ',') !== false) {
            $myDependentFields = explode(',', $G_FORM->fields[$k]->dependentFields);
        } else {
            $myDependentFields = explode('|', $G_FORM->fields[$k]->dependentFields);
        }
        for ($r = 0; $r < count($myDependentFields); $r++) {
            if ($myDependentFields[$r] == "") {
                unset($myDependentFields[$r]);
            }
        }
        $mD = $myDependentFields;
        foreach ($mD as $ki) {
            $myDependentFields = array_merge($myDependentFields, subDependencies($ki, $G_FORM, $aux));
        }
    } else {
        if (!isset($G_FORM->fields[$grid])) {
            return array();
        }
        if (!array_key_exists($k, $G_FORM->fields[$grid]->fields)) {
            return array();
        }
        if (!isset($G_FORM->fields[$grid]->fields[$k]->dependentFields)) {
            return array();
        }

        $aux[] = $k;

        if (strpos($G_FORM->fields[$grid]->fields[$k]->dependentFields, ',') !== false) {
            $myDependentFields = explode(',', $G_FORM->fields[$grid]->fields[$k]->dependentFields);
        } else {
            $myDependentFields = explode('|', $G_FORM->fields[$grid]->fields[$k]->dependentFields);
        }

        for ($r = 0; $r < count($myDependentFields); $r++) {
            if ($myDependentFields[$r] == "") {
                unset($myDependentFields[$r]);
            }
        }

        $mD = $myDependentFields;

        foreach ($mD as $ki) {
            $myDependentFields = array_merge($myDependentFields, subDependencies($ki, $G_FORM, $aux, $grid));
        }

        //Set field and the dependent field of the grid
        foreach ($myDependentFields as $index => $value) {
            $myDependentFields[$index] = $k . "|" . $value;
        }
    }

    return $myDependentFields;
}
