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
                try {
                    if ($id) {
                        $result = $this->patient->getPatientById($id);
                    } else {
                        $result = $this->patient->getAllPatients();
                    }

                    // Check if result is empty
                    if (!$result) {
                        Response::send(false, "No patients found", [], 404);
                    }

                    Response::send(true, "Data retrieved successfully", $result);
                } catch (Exception $e) {
                    // Log the error for the developer (optional)
                    error_log($e->getMessage());

                    // Send a professional error message to the user
                    Response::send(false, "Server error: Database connection failed", [], 500);
                }
                break;

            case 'POST':
                // 1. Check if required fields are present
                if (empty($data['name']) || empty($data['age']) || empty($data['phone'])) {
                    Response::send(false, "Missing required fields", [], 400);
                }

                // 2. Validate Age (Must be a positive number)
                if (!is_numeric($data['age']) || $data['age'] <= 0) {
                    Response::send(false, "Invalid age. Must be a positive number.", [], 400);
                }

                // 3. Validate Phone Format (Exactly 10 digits)
                if (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
                    Response::send(false, "Invalid phone number. Must be exactly 10 digits.", [], 400);
                }

                // 4. Check for Duplicate Data (New Feature)
                if ($this->patient->isDuplicatePhone($data['phone'])) {
                    Response::send(false, "Phone number already exists. Cannot add duplicate patient.", [], 400);
                }

                // If all checks pass, proceed to save
                try {
                    if ($this->patient->createPatient($data)) {
                        Response::send(true, "Patient created successfully", [], 201);
                    } else {
                        Response::send(false, "Failed to create patient", [], 500);
                    }
                } catch (Exception $e) {
                    // Global Error Handling
                    Response::send(false, "Server error: " . $e->getMessage(), [], 500);
                }
                break;
            case 'PUT':
                if (!$id) {
                    Response::send(false, "ID required for update", [], 400);
                }

                // 1. Check if all required fields are provided for a full update
                if (empty($data['name']) || empty($data['age']) || empty($data['phone'])) {
                    Response::send(false, "All fields (name, age, phone) are required for PUT update", [], 400);
                }

                try {
                    // 2. Validate Age (Positive number)
                    if (!is_numeric($data['age']) || $data['age'] <= 0) {
                        Response::send(false, "Invalid age. Must be a positive number.", [], 400);
                    }

                    // 3. Validate Phone Format (10 digits)
                    if (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
                        Response::send(false, "Invalid phone number. Must be exactly 10 digits.", [], 400);
                    }

                    // 4. Duplicate Check (Exclude current ID)
                    if ($this->patient->isDuplicatePhone($data['phone'], $id)) {
                        Response::send(false, "This phone number is already assigned to another patient.", [], 400);
                    }

                    // 5. Execute Update
                    if ($this->patient->updatePatient($id, $data)) {
                        Response::send(true, "Patient updated successfully");
                    } else {
                        // Update failed usually means no changes were made or ID doesn't exist
                        Response::send(false, "Update failed. Patient may not exist or no changes made.", [], 404);
                    }
                } catch (Exception $e) {
                    // Global Error Handling for DB issues on Port 3308
                    Response::send(false, "Server error: " . $e->getMessage(), [], 500);
                }
                break;

            case 'PATCH':
                if ($id && !empty($data)) {
                    try {
                        // 1. Phone number validation and duplicate check
                        if (isset($data['phone'])) {
                            // Check format (10 digits)
                            if (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
                                Response::send(false, "Invalid phone number. Must be exactly 10 digits.", [], 400);
                            }

                            // Check for duplicate (Exclude current ID)
                            // Intha line namma update panra number vera yaaru kittayaachum irukkanu check pannum.
                            if ($this->patient->isDuplicatePhone($data['phone'], $id)) {
                                Response::send(false, "This phone number is already assigned to another patient.", [], 400);
                            }
                        }

                        // 2. Age validation
                        if (isset($data['age'])) {
                            if (!is_numeric($data['age']) || $data['age'] <= 0) {
                                Response::send(false, "Invalid age. Must be a positive number.", [], 400);
                            }
                        }

                        // Validation pass aana database-ku anuppuvom
                        if ($this->patient->patchPatient($id, $data)) {
                            Response::send(true, "Patient partially updated successfully");
                        } else {
                            Response::send(false, "Update failed", [], 500);
                        }
                    } catch (Exception $e) {
                        // Global error handling
                        Response::send(false, "Server error: " . $e->getMessage(), [], 500);
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
