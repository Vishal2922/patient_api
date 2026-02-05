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
                    $res ? Response::send(true, "Patient found", $res)
                        : Response::send(false, "Patient not found", [], 404);
                } else {
                    Response::send(true, "Patients retrieved", $this->patient->getAllPatients());
                }
                break;

            case 'POST':
                // 1. Check if required fields are present
                if (empty($data['name']) || empty($data['email'])) {
                    Response::send(false, "Missing required fields: name and email", [], 400);
                    break;
                }
                // 2. Attempt creation
                if ($this->patient->createPatient($data)) {
                    Response::send(true, "Patient created successfully", [], 201);
                } else {
                    Response::send(false, "Failed to create patient. Database error.", [], 500);
                }
                break;

            case 'PUT':
                if (!$id) {
                    Response::send(false, "ID required for update", [], 400);
                } elseif (empty($data)) {
                    Response::send(false, "No data provided for update", [], 400);
                } elseif ($this->patient->updatePatient($id, $data)) {
                    Response::send(true, "Patient updated successfully");
                } else {
                    Response::send(false, "Update failed. Patient may not exist or no changes made.", [], 404);
                }
                break;

            case 'PATCH':
                if ($id && !empty($data)) {
                    if ($this->patient->patchPatient($id, $data)) {
                        Response::send(true, "Patient partially updated successfully");
                    } else {
                        Response::send(false, "Partial update failed", [], 500);
                    }
                } else {
                    Response::send(false, "ID and data required", [], 400);
                }
                break;

            case 'DELETE':
                if (!$id) {
                    Response::send(false, "ID required for deletion", [], 400);
                } elseif ($this->patient->deletePatient($id)) {
                    Response::send(true, "Patient deleted successfully");
                } else {
                    Response::send(false, "Delete failed. Patient not found.", [], 404);
                }
                break;

            default:
                Response::send(false, "Method not allowed", [], 405);
        }
    }
}
