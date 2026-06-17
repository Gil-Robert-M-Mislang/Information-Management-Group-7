<?php
include 'database.php';

$conn->begin_transaction();

echo $_SERVER['REQUEST_METHOD'];
echo "<pre>";
print_r($_POST);
echo "</pre>";

try {
    $ApplicantID = $_POST['ApplicantID'];
    echo "ApplicantID: $ApplicantID<br>";

    $contactNo = $_POST['applicant_t']['ContactNo'] ?? '';

    if (!preg_match('/^\d{11}$/', $contactNo)) {
        throw new Exception("Contact number must be exactly 11 digits.");
    }

    echo "Contact number valid<br>";

    // ==========================
    // SCHOOL
    // ==========================
    $schoolIDs = [];

    for ($i = 0; $i < 4; $i++) {

        echo "School loop $i<br>";

        $name = $_POST['school_t']['SchoolName'][$i];
        $address = $_POST['school_t']['SchoolAddress'][$i];

        $check = $conn->prepare("
            SELECT SchoolID
            FROM school_t
            WHERE SchoolName = ?
            AND SchoolAddress = ?
        ");

        $check->bind_param("ss", $name, $address);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $schoolIDs[$i] = $row['SchoolID'];
        } else {

            $last = mysqli_query(
                $conn,
                "SELECT SchoolID
                 FROM school_t
                 ORDER BY SchoolID DESC
                 LIMIT 1"
            );

            $lastRow = mysqli_fetch_assoc($last);

            $num = $lastRow
                ? preg_replace('/[^0-9]/', '', $lastRow['SchoolID']) + 1
                : 1;

            $newID = 'S' . $num;

            $ins = $conn->prepare("
                INSERT INTO school_t
                (SchoolID, SchoolName, SchoolAddress)
                VALUES (?, ?, ?)
            ");

            $ins->bind_param(
                "sss",
                $newID,
                $name,
                $address
            );

            $ins->execute();

            $schoolIDs[$i] = $newID;
        }
    }

    echo "Schools done<br>";

    // ==========================
    // APPLICANT
    // ==========================
    $ap = $_POST['applicant_t'];

    $stmt = $conn->prepare("
        UPDATE applicant_t SET
            ApplicantName = ?,
            Sex = ?,
            Citizenship = ?,
            IndigenousGroup = ?,
            CivilStatus = ?,
            ContactNo = ?,
            BirthPlace = ?,
            BirthOrder = ?,
            BirthDate = ?,
            PermanentAddress = ?,
            HouseOwnership = ?,
            GrossMonthlyFamilyIncome = ?,
            CollegeID = ?,
            Course = ?,
            SHSID = ?,
            SHSStrand = ?,
            JHSID = ?,
            ElemID = ?
        WHERE ApplicantID = ?
    ");

    $stmt->bind_param(
        "sssssssssssssssssss",
        $ap['ApplicantName'],
        $ap['Sex'],
        $ap['Citizenship'],
        $ap['IndigenousGroup'],
        $ap['CivilStatus'],
        $ap['ContactNo'],
        $ap['BirthPlace'],
        $ap['BirthOrder'],
        $ap['BirthDate'],
        $ap['PermanentAddress'],
        $ap['HouseOwnership'],
        $ap['GrossMonthlyFamilyIncome'],
        $schoolIDs[0],
        $ap['Course'],
        $schoolIDs[1],
        $ap['SHSStrand'],
        $schoolIDs[2],
        $schoolIDs[3],
        $ApplicantID
    );

    $stmt->execute();

    echo "Applicant updated<br>";

    $guardianIDs = $_POST['guardianID'];

    $pgCount = count($guardianIDs);

    for ($i = 0; $i < $pgCount; $i++) {

        echo "Guardian loop $i<br>";

        $pgID = $guardianIDs[$i];

        $pgStmt = $conn->prepare("
            UPDATE parentguardian_t SET
                ParentGuardianName = ?,
                Address = ?,
                Occupation = ?,
                EducationalAttainment = ?
            WHERE ParentGuardianID = ?
        ");

        $pgStmt->bind_param(
            "sssss",
            $_POST['parentguardian']['ParentGuardianName'][$i],
            $_POST['parentguardian_t']['Address'][$i],
            $_POST['parentguardian_t']['Occupation'][$i],
            $_POST['parentguardian_t']['EducationalAttainment'][$i],
            $pgID
        );

        $pgStmt->execute();

        echo "ParentGuardian updated<br>";

        $isEarner =
            (int) $_POST['applicantguardian_t']['IsIncomeEarner'][$i];

        $rel =
            $_POST['applicantguardian_t']['Relationship'][$i];

        $agStmt = $conn->prepare("
            UPDATE applicantguardian_t SET
                Relationship = ?,
                IsIncomeEarner = ?
            WHERE ApplicantID = ?
            AND ParentGuardianID = ?
        ");

        $agStmt->bind_param(
            "siss",
            $rel,
            $isEarner,
            $ApplicantID,
            $pgID
        );

        $agStmt->execute();

        echo "ApplicantGuardian updated<br>";
    }

    echo "About to commit<br>";

    $conn->commit();

    echo "COMMIT SUCCESSFUL";
    exit();

} catch (Exception $e) {

    $conn->rollback();

    echo "<h2>ERROR</h2>";
    echo $e->getMessage();
}
?>