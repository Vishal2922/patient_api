<?php
class Patient
{
    private $conn;
    private $table = "patients";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllPatients()
    {
        $result = $this->conn->query("SELECT * FROM " . $this->table);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPatientById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createPatient($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table . " (name, age, gender, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $data['name'], $data['age'], $data['gender'], $data['phone']);
        return $stmt->execute();
    }

    public function updatePatient($id, $data)
    {
        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET name=?, age=?, gender=?, phone=? WHERE id=?");
        $stmt->bind_param("sissi", $data['name'], $data['age'], $data['gender'], $data['phone'], $id);
        return $stmt->execute();
    }

    public function patchPatient($id, $data)
    {
        $fields = "";
        $types = "";
        $values = [];

        foreach ($data as $key => $value) {
            $fields .= "$key=?, ";
            $types .= is_int($value) ? "i" : "s";
            $values[] = $value;
        }

        $fields = rtrim($fields, ", ");
        $types .= "i"; // For the ID
        $values[] = $id;

        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET $fields WHERE id=?");
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function deletePatient($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
