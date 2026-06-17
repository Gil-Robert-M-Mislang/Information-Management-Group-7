<?php
    include 'database.php';

    $recordDelete = isset($_POST['ApplicantID']) ? $_POST['ApplicantID'] : null;

    $ApplicantID = $_POST['ApplicantID'];

    $deleteApplicantGuardian = $conn->prepare("DELETE FROM applicantguardian_t WHERE ApplicantID = ?");
    $deleteApplicantGuardian->bind_param("s", $ApplicantID);
    $deleteApplicantGuardian->execute();

    $deleteApplicant = $conn->prepare("DELETE FROM applicant_t WHERE ApplicantID = ?");
    $deleteApplicant->bind_param("s", $ApplicantID);
    $deleteApplicant->execute();

    if ($deleteApplicantGuardian->affected_rows > 0 || $deleteApplicant->affected_rows > 0) {
       echo "Record with ApplicantID: " . $ApplicantID . " has been deleted.";
    } else {
        echo "No record found with ApplicantID: " . $ApplicantID;
    }
?>