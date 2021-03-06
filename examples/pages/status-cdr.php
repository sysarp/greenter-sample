<?php
/**
 * Created by PhpStorm.
 * User: Giansalex
 * Date: 09/09/2018
 * Time: 12:48
 */

use Greenter\Ws\Services\ExtService;
use Greenter\Ws\Services\SoapClient;
use Greenter\Ws\Services\SunatEndpoints;

require __DIR__ . '/../../vendor/autoload.php';

$errorMsg = null;

function validateFields(array $items)
{
    global $errorMsg;
    foreach ($items as $key => $value) {
        if (empty($value)) {
            $errorMsg = 'El campo '.$key.', es requerido';
            return false;
        }
    }

    return true;
}

function getCdrStatusService($user, $password)
{
    $ws = new SoapClient(SunatEndpoints::FE_CONSULTA_CDR.'?wsdl');
    $ws->setCredentials($user, $password);

    $service = new ExtService();
    $service->setClient($ws);

    return $service;
}

function process($fields)
{
    if (!isset($fields['rucSol'])) {
        return null;
    }

    if (!validateFields($fields)) {
        return null;
    }

    $service = getCdrStatusService($fields['rucSol'].$fields['userSol'], $fields['passSol']);
    return $service->getCdrStatus($fields['ruc'], $fields['tipo'], $fields['serie'], intval($fields['numero']));
}

$result = process($_POST);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../../views/head.php'; ?>
    <style>
        .mb-100 {
            margin-bottom: 100px;
        }
    </style>
</head>
<body>
<?php include '../../views/top.php'; ?>
<div class="container mb-100">
    <div class="row">
        <?php if (isset($result)): ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">Resultado</div>
                    <div class="card-block">
                        <div class="card bg-light text-dark">
                            <div class="card-body">
                                <?php if ($result->isSuccess()): ?>
                                    <strong>Codigo: </strong> <?=$result->getCode()?> <br>
                                    <strong>Mensaje: </strong> <?=$result->getMessage()?> <br>
                                    <?php if (!is_null($result->getCdrResponse())):?>
                                        <strong>CDR Mensaje: </strong> <?=$result->getCdrResponse()->getDescription()?>
                                        <br>
                                        <strong>Observaciones: </strong> <?=implode('<br>', $result->getCdrResponse()->getNotes())?>
                                        <br>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-danger">
                                        <?=$result->getError()->getMessage()?>
                                    </div>
                                <?php endif;?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-md-12">
            <div class="card bg-primary">
                <div class="card-header text-white">Consulta de CDR Status</div>
                <div class="card-block">
                    <div class="card bg-light text-dark">
                        <div class="card-body">
                            <?php if (isset($errorMsg)):?>
                                <div class="alert alert-danger">
                                    <?=$errorMsg?>
                                </div>
                            <?php endif; ?>
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Credenciales</strong>
                                        <div class="form-group">
                                            <label for="rucSol">Ruc:</label>
                                            <input type="text" class="form-control" name="rucSol" id="rucSol" maxlength="11">
                                        </div>
                                        <div class="form-group">
                                            <label for="userSol">Usuario:</label>
                                            <input type="text" class="form-control" name="userSol" id="userSol">
                                        </div>
                                        <div class="form-group">
                                            <label for="passSol">Contraseña:</label>
                                            <input type="password" class="form-control" name="passSol" id="passSol">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Datos del Comprobante</strong>
                                        <div class="form-group">
                                            <label for="ruc">Ruc Emisor:</label>
                                            <input type="text" class="form-control" name="ruc" id="ruc"
                                                   value="20000000001"
                                                   maxlength="11">
                                        </div>
                                        <div class="form-group">
                                            <label for="tipo">Tipo:</label>
                                            <input type="text" class="form-control" name="tipo" id="tipo" value="01" maxlength="2">
                                        </div>
                                        <div class="form-group">
                                            <label for="serie">Serie:</label>
                                            <input type="text" class="form-control" name="serie" id="serie" value="F001" maxlength="4">
                                        </div>
                                        <div class="form-group">
                                            <label for="numero">Correlativo:</label>
                                            <input type="number" class="form-control" name="numero" id="numero" value="1" min="1">
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-success">Consultar CDR</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../../views/footer.php'; ?>
</body>
</html>

