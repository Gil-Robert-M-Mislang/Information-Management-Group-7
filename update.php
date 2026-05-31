<?php
include 'database.php';

$conn->begin_transaction();

try {
    $ApplicantID = mysqli_real_escape_string($conn, $_POST['ApplicantID']);

    // school 
    $schoolIDs = [];
    for ($i = 0; $i < 4; $i++) {
        $name    = $_POST['school']['SchoolName'][$i];
        $address = $_POST['school']['SchoolAddress'][$i];

        $check = mysqli_query($conn, "SELECT SchoolID FROM school WHERE SchoolName = '" . mysqli_real_escape_string($conn, $name) . "' AND SchoolAddress = '" . mysqli_real_escape_string($conn, $address) . "'");

        if (mysqli_num_rows($check) > 0) {
            $schoolIDs[$i] = mysqli_fetch_assoc($check)['SchoolID'];
        } else {
            $last = mysqli_query($conn, "SELECT SchoolID FROM school ORDER BY CAST(REGEXP_REPLACE(SchoolID,'[^0-9]','') AS UNSIGNED) DESC LIMIT 1");
            $lastRow = mysqli_fetch_assoc($last);
            $num = $lastRow ? preg_replace('/[^0-9]/', '', $lastRow['SchoolID']) + 1 : 1;
            $newID = 'S' . $num;

            $ins = $conn->prepare("INSERT INTO school (SchoolID, SchoolName, SchoolAddress) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $newID, $name, $address);
            $ins->execute();
            $schoolIDs[$i] = $newID;
        }
    }

    // applicant
    $ap = $_POST['applicant'];
    $stmt = $conn->prepare("
        UPDATE applicant SET
            ApplicantName = ?,
            Sex = ?,
            Citizenship = ?,
            IndigenousGroup = ?,
            CivilStatus = ?,
            ContactNo = ?,
            EmailAddress = ?,
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
        "ssssssssssssssssssss",
        $ap['ApplicantName'],
        $ap['Sex'],
        $ap['Citizenship'],
        $ap['IndigenousGroup'],
        $ap['CivilStatus'],
        $ap['ContactNo'],
        $ap['EmailAddress'],
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

    // guardians
    $guardianIDs = $_POST['guardianID'];
    $pgCount = count($guardianIDs);

    for ($i = 0; $i < $pgCount; $i++) {
        $pgID = $guardianIDs[$i];

        $pgStmt = $conn->prepare("
            UPDATE parentguardian SET
                ParentGuardianName = ?,
                Address = ?,
                Occupation = ?,
                EducationalAttainment = ?
            WHERE ParentGuardianID = ?
        ");
        $pgStmt->bind_param(
            "sssss",
            $_POST['parentguardian']['ParentGuardianName'][$i],
            $_POST['parentguardian']['Address'][$i],
            $_POST['parentguardian']['Occupation'][$i],
            $_POST['parentguardian']['EducationalAttainment'][$i],
            $pgID
        );
        $pgStmt->execute();

        $isEarner = $_POST['applicantguardian']['IsIncomeEarner'][$i];
        $rel = $_POST['applicantguardian']['Relationship'][$i];

        $agStmt = $conn->prepare("
            UPDATE applicantguardian SET
                Relationship = ?,
                IsIncomeEarner = ?
            WHERE ApplicantID = ? AND ParentGuardianID = ?
        ");
        $agStmt->bind_param("siss", $rel, $isEarner, $ApplicantID, $pgID);
        $agStmt->execute();
    }

    $conn->commit();
    mysqli_close($conn);

    header("Location: menu.html?updated=1");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>