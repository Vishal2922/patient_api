<?php
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../helpers/Response.php';

class PatientController
{
    private $patient;

    public function __construct($db)
    {
        $this->patient = new Patient($db);
    }

    public function handleRequest($method, $id, $data)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $res = $this->patient->getPatientById($id);
                    $res ? Response::send(true, "Patient found", $res) : Response::send(false, "Patient not found", [], 404);
                } else {
                    Response::send(true, "Patients retrieved", $this->patient->getAllPatients());
                }
                break;
            case 'POST':
                if ($this->patient->createPatient($data)) {
                    Response::send(true, "Patient created successfully", [], 201);
                }
                break;
            case 'PUT':
                if ($id && $this->patient->updatePatient($id, $data)) {
                    Response::send(true, "Patient updated successfully");
                }
                break;

            case 'PATCH':
                if ($id && !empty($data)) {
                    if ($this->patient->patchPatient($id, $data)) {
                        Response::send(true, "Patient partially updated successfully");
                    } else {
                        Response::send(false, "Update failed", [], 500);
                    }
                } else {
                    Response::send(false, "ID and data required", [], 400);
                }
                break;
                
            case 'DELETE':
                if ($id && $this->patient->deletePatient($id)) {
                    Response::send(true, "Patient deleted successfully");
                }
                break;
            default:
                Response::send(false, "Method not allowed", [], 405);
        }
    }
}
