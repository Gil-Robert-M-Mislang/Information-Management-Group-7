<?php
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    include 'database.php';

    $conn->begin_transaction();

    $allowedFields = [
        "applicant" => "ApplicantID, ApplicantName, Sex, Citizenship, IndigenousGroup, CivilStatus, ContactNo, EmailAddress, BirthPlace, BirthOrder, BirthDate, PermanentAddress, HouseOwnership, GrossMonthlyFamilyIncome, CollegeID, Course, SHSID, SHSStrand, JHSID, ElemID",
        "school" => "SchoolID, SchoolName, SchoolAddress",
        "applicantguardian" => "ApplicantID, ParentGuardianID, Relationship, IsIncomeEarner",
        "parentguardian" => "ParentGuardianID, ParentGuardianName, Address, Occupation, EducationalAttainment"
    ];

   try {

        //Error checking for the email address
        $search = isset($_GET['emailInput']) ? $_GET['emailInput'] : '';
        if (!empty($search)) {
            $search = mysqli_real_escape_string($conn, $search);
            $emailCheckQuery = "SELECT * FROM applicant_t WHERE EmailAddress = '$search'";
            $emailCheckResult = mysqli_query($conn, $emailCheckQuery);

            if (mysqli_num_rows($emailCheckResult) > 0) {
                echo "emailExists";
                exit();
            }
        }

        // Increment the ApplicantID
        $lastRecord = mysqli_query($conn, "SELECT * FROM applicant_t ORDER BY REGEXP_REPLACE(ApplicantID, '[0-9]', ''), CAST(REGEXP_REPLACE(ApplicantID, '[^0-9]', '') AS UNSIGNED) DESC LIMIT 1");
        $lastId = mysqli_fetch_assoc($lastRecord)['ApplicantID'];
        $number = preg_replace('/[^0-9]/', '', $lastId) + 1;
        $ApplicantID = 'A' . $number;

        /*
            PARENT GUARDIAN SECTION
        */

        $parentGuardianIDs = [];

        $parentGuardianCount = count($_POST['parentguardian']['ParentGuardianName'] ?? []);

        for($i = 0; $i < $parentGuardianCount; $i++) {

            $searchParentGuardian = mysqli_query(
                $conn,
                "SELECT *
                FROM parentguardian_t
                WHERE ParentGuardianName = '" . $_POST['parentguardian']['ParentGuardianName'][$i] . "'
                AND Address = '" . $_POST['parentguardian']['Address'][$i] . "'
                AND Occupation = '" . $_POST['parentguardian']['Occupation'][$i] . "'
                AND EducationalAttainment = '" . $_POST['parentguardian']['EducationalAttainment'][$i] . "'"
            );

            if(mysqli_num_rows($searchParentGuardian) > 0) {

                $parentGuardianData = mysqli_fetch_assoc($searchParentGuardian);

                $ParentGuardianID = $parentGuardianData['ParentGuardianID'];

            } else {

                $lastRecord = mysqli_query(
                    $conn,
                    "SELECT *
                    FROM parentguardian_t
                    ORDER BY
                    CAST(REGEXP_REPLACE(ParentGuardianID, '[^0-9]', '') AS UNSIGNED) DESC
                    LIMIT 1"
                );

                $row = mysqli_fetch_assoc($lastRecord);

                if($row) {

                    $lastId = $row['ParentGuardianID'];

                    $number = preg_replace('/[^0-9]/', '', $lastId) + 1;

                } else {

                    $number = 1;
                }

                $ParentGuardianID = 'PG' . $number;

                $insertParentGuardian = $conn->prepare(
                    "INSERT INTO parentguardian_t
                    (" . $allowedFields['parentguardian'] . ")
                    VALUES (?, ?, ?, ?, ?)"
                );

                $insertParentGuardian->bind_param(
                    "sssss",
                    $ParentGuardianID,
                    $_POST['parentguardian']['ParentGuardianName'][$i],
                    $_POST['parentguardian']['Address'][$i],
                    $_POST['parentguardian']['Occupation'][$i],
                    $_POST['parentguardian']['EducationalAttainment'][$i]
                );

                $insertParentGuardian->execute();
            }

            // STORE EACH ID
            $parentGuardianIDs[$i] = $ParentGuardianID;
        }



        /*
            SCHOOL SECTION
        */

        for($i = 0; $i < 4; $i++) {
            $searchSchool = mysqli_query($conn, "SELECT * FROM school_t WHERE SchoolName = '" . $_POST['school']['SchoolName'][$i] . "' AND SchoolAddress = '" . $_POST['school']['SchoolAddress'][$i] . "'");
            
            if(mysqli_num_rows($searchSchool) > 0) {
                echo "Existing School record found for SchoolName: " . $_POST['school']['SchoolName'][$i] . " and SchoolAddress: " . $_POST['school']['SchoolAddress'][$i];
            } else {
                $lastRecord = mysqli_query($conn, "SELECT * FROM school_t ORDER BY REGEXP_REPLACE(SchoolID, '[0-9]', ''), CAST(REGEXP_REPLACE(SchoolID, '[^0-9]', '') AS UNSIGNED) DESC LIMIT 1");
                $lastId = mysqli_fetch_assoc($lastRecord)['SchoolID'];
                $number = preg_replace('/[^0-9]/', '', $lastId) + 1;
                $SchoolID = 'S' . $number;
                echo $SchoolID;

                // Insert into the school table
                $insertSchool = $conn->prepare("INSERT INTO school_t (" . $allowedFields['school'] . ") VALUES (?, ?, ?)");
                $insertSchool->bind_param(
                    "sss",
                    $SchoolID,
                    $_POST['school']['SchoolName'][$i],
                    $_POST['school']['SchoolAddress'][$i]
                );
                $insertSchool->execute();
            }
        }

        /*
            APPLICANT SECTION
        */
        $CollegeIDRecord = mysqli_query($conn, "SELECT SchoolID FROM school_t WHERE SchoolName = '" . $_POST['school']['SchoolName'][0] . "' AND SchoolAddress = '" . $_POST['school']['SchoolAddress'][0] . "'");
        $CollegeID = mysqli_fetch_assoc($CollegeIDRecord)['SchoolID'];

        $SHSIDRecord = mysqli_query($conn, "SELECT SchoolID FROM school_t WHERE SchoolName = '" . $_POST['school']['SchoolName'][1] . "' AND SchoolAddress = '" . $_POST['school']['SchoolAddress'][1] . "'"); 
        $SHSID = mysqli_fetch_assoc($SHSIDRecord)['SchoolID'];

        $JHSIDRecord = mysqli_query($conn, "SELECT SchoolID FROM school_t WHERE SchoolName = '" . $_POST['school']['SchoolName'][2] . "' AND SchoolAddress = '" . $_POST['school']['SchoolAddress'][2] . "'");
        $JHSID = mysqli_fetch_assoc($JHSIDRecord)['SchoolID'];

        $ElemIDRecord = mysqli_query($conn, "SELECT SchoolID FROM school_t WHERE SchoolName = '" . $_POST['school']['SchoolName'][3] . "' AND SchoolAddress = '" . $_POST['school']['SchoolAddress'][3] . "'");
        $ElemID = mysqli_fetch_assoc($ElemIDRecord)['SchoolID'];

        
        $IndigenousGroup = $_POST['applicant']['IndigenousGroup'] ?? '';

        // Insert into the applicant table
        $insertApplicant = $conn->prepare(
            "INSERT INTO applicant_t
            (" . $allowedFields['applicant'] . ")
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $insertApplicant->bind_param(
            "ssssssssssssssssssss",
            $ApplicantID,
            $_POST['applicant']['ApplicantName'],
            $_POST['applicant']['Sex'],
            $_POST['applicant']['Citizenship'],
            $IndigenousGroup,
            $_POST['applicant']['CivilStatus'],
            $_POST['applicant']['ContactNo'],
            $_POST['applicant']['EmailAddress'],
            $_POST['applicant']['BirthPlace'],
            $_POST['applicant']['BirthOrder'],
            $_POST['applicant']['BirthDate'],
            $_POST['applicant']['PermanentAddress'],
            $_POST['applicant']['HouseOwnership'],
            $_POST['applicant']['GrossMonthlyFamilyIncome'],
            $CollegeID,
            $_POST['applicant']['Course'],
            $SHSID,
            $_POST['applicant']['SHSStrand'],
            $JHSID,
            $ElemID
        );
        $insertApplicant->execute();

        
        /*
            APPLICANT GUARDIAN SECTION
        */

        $applicantGuardianCount = count($_POST['applicantguardian']['Relationship']);

        for($i = 0; $i < $applicantGuardianCount; $i++) {

            $relationship = $_POST['applicantguardian']['Relationship'][$i] ?? '';

            $isIncomeEarner = $_POST['applicantguardian']['IsIncomeEarner'][$i] ?? '';

            $searchApplicantGuardian = mysqli_query(
                $conn,
                "SELECT *
                FROM applicantguardian_t
                WHERE ApplicantID = '$ApplicantID'
                AND ParentGuardianID = '" . $parentGuardianIDs[$i] . "'"
            );

            if(mysqli_num_rows($searchApplicantGuardian) > 0) {

                echo "Existing ApplicantGuardian record found for ApplicantID: "
                . $ApplicantID .
                " and ParentGuardianID: "
                . $parentGuardianIDs[$i] .
                "<br>";

            } else {

                echo $ApplicantID . " - " . $parentGuardianIDs[$i] . "<br>";
                $IsIncomeEarner = isset($_POST['applicantguardian']['IsIncomeEarner'][$i]) ? 1 : 0;

                $insertApplicantGuardian = $conn->prepare(
                    "INSERT INTO applicantguardian_t
                    (" . $allowedFields['applicantguardian'] . ")
                    VALUES (?, ?, ?, ?)"
                );

                $insertApplicantGuardian->bind_param(
                    "ssss",
                    $ApplicantID,
                    $parentGuardianIDs[$i],
                    $relationship,
                    $IsIncomeEarner
                );

                $insertApplicantGuardian->execute();
            }
        }

        $conn->commit();
        mysqli_close($conn);
   }
   catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
    
    header("Location: menu.html");
    exit();
?>