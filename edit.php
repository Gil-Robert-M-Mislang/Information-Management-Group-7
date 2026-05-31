<?php
include 'database.php';

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($id)) {
    echo "<p>Invalid applicant ID.</p>";
    exit();
}

// Fetch applicant and school info
$applicantQuery = mysqli_query($conn, "
    SELECT a.*,
        c.SchoolName AS CollegeName, c.SchoolAddress AS CollegeAddress,
        s.SchoolName AS SHSName, s.SchoolAddress AS SHSAddress,
        j.SchoolName AS JHSName, j.SchoolAddress AS JHSAddress,
        e.SchoolName AS ElemName, e.SchoolAddress AS ElemAddress
    FROM applicant a
    LEFT JOIN school c ON a.CollegeID = c.SchoolID
    LEFT JOIN school s ON a.SHSID = s.SchoolID
    LEFT JOIN school j ON a.JHSID = j.SchoolID
    LEFT JOIN school e ON a.ElemID = e.SchoolID
    WHERE a.ApplicantID = '$id'
");

$a = mysqli_fetch_assoc($applicantQuery);

if (!$a) {
    echo "<p>Applicant not found.</p>";
    exit();
}

// Fetch guardians
$guardianQuery = mysqli_query($conn, "
    SELECT pg.*, ag.Relationship, ag.IsIncomeEarner
    FROM parentguardian pg
    JOIN applicantguardian ag ON pg.ParentGuardianID = ag.ParentGuardianID
    WHERE ag.ApplicantID = '$id'
");

$guardians = [];
while ($row = mysqli_fetch_assoc($guardianQuery)) {
    $guardians[] = $row;
}
?>

<h2 class="modal-title">Edit Applicant</h2>

<form id="editForm" action="update.php" method="POST">
    <input type="hidden" name="ApplicantID" value="<?= htmlspecialchars($a['ApplicantID']) ?>">

    <div class="view-section">
        <h3>Personal Data</h3>
        <div class="view-grid">
            <div class="view-field">
                <label class="view-label">Complete Name</label>
                <input class="edit-input" type="text" name="applicant[ApplicantName]" value="<?= htmlspecialchars($a['ApplicantName']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Permanent Address</label>
                <input class="edit-input" type="text" name="applicant[PermanentAddress]" value="<?= htmlspecialchars($a['PermanentAddress']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Date of Birth</label>
                <input class="edit-input" type="date" name="applicant[BirthDate]" value="<?= htmlspecialchars($a['BirthDate']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Place of Birth</label>
                <input class="edit-input" type="text" name="applicant[BirthPlace]" value="<?= htmlspecialchars($a['BirthPlace']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Birth Order</label>
                <input class="edit-input" type="text" name="applicant[BirthOrder]" value="<?= htmlspecialchars($a['BirthOrder']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Email Address</label>
                <input class="edit-input" type="email" name="applicant[EmailAddress]" value="<?= htmlspecialchars($a['EmailAddress']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Contact Number</label>
                <input class="edit-input" type="text" name="applicant[ContactNo]" value="<?= htmlspecialchars($a['ContactNo']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Citizenship</label>
                <input class="edit-input" type="text" name="applicant[Citizenship]" value="<?= htmlspecialchars($a['Citizenship']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Indigenous Group</label>
                <input class="edit-input" type="text" name="applicant[IndigenousGroup]" value="<?= htmlspecialchars($a['IndigenousGroup']) ?>">
            </div>
            <div class="view-field">
                <label class="view-label">Sex</label>
                <div class="radio-group-edit">
                    <?php foreach (['Female','Male','Prefer not to say'] as $opt): ?>
                    <label>
                        <input type="radio" name="applicant[Sex]" value="<?= $opt ?>" <?= strtolower($a['Sex']) === strtolower($opt) ? 'checked' : '' ?>>
                        <?= $opt ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="view-field">
                <label class="view-label">Civil Status</label>
                <div class="radio-group-edit">
                    <?php foreach (['Single','Married','Widowed'] as $opt): ?>
                    <label>
                        <input type="radio" name="applicant[CivilStatus]" value="<?= $opt ?>" <?= $a['CivilStatus'] === $opt ? 'checked' : '' ?>>
                        <?= $opt ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="view-section">
        <h3>Educational Background</h3>
        <div class="view-grid">
            <div class="view-field">
                <label class="view-label">College Name</label>
                <input class="edit-input" type="text" name="school[SchoolName][0]" value="<?= htmlspecialchars($a['CollegeName']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">College Address</label>
                <input class="edit-input" type="text" name="school[SchoolAddress][0]" value="<?= htmlspecialchars($a['CollegeAddress']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Course</label>
                <input class="edit-input" type="text" name="applicant[Course]" value="<?= htmlspecialchars($a['Course']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Senior High School</label>
                <input class="edit-input" type="text" name="school[SchoolName][1]" value="<?= htmlspecialchars($a['SHSName']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">SHS Address</label>
                <input class="edit-input" type="text" name="school[SchoolAddress][1]" value="<?= htmlspecialchars($a['SHSAddress']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">SHS Strand</label>
                <input class="edit-input" type="text" name="applicant[SHSStrand]" value="<?= htmlspecialchars($a['SHSStrand']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Junior High School</label>
                <input class="edit-input" type="text" name="school[SchoolName][2]" value="<?= htmlspecialchars($a['JHSName']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">JHS Address</label>
                <input class="edit-input" type="text" name="school[SchoolAddress][2]" value="<?= htmlspecialchars($a['JHSAddress']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Elementary School</label>
                <input class="edit-input" type="text" name="school[SchoolName][3]" value="<?= htmlspecialchars($a['ElemName']) ?>" required>
            </div>
            <div class="view-field">
                <label class="view-label">Elementary Address</label>
                <input class="edit-input" type="text" name="school[SchoolAddress][3]" value="<?= htmlspecialchars($a['ElemAddress']) ?>" required>
            </div>
        </div>
    </div>

    <div class="view-section">
        <h3>Socio-Economic Background</h3>
        <div class="view-grid">
            <div class="view-field">
                <label class="view-label">House Ownership</label>
                <div class="radio-group-edit">
                    <?php foreach (['Owned','Rented','Living with Relatives','Mortgaged/Amortized','Others'] as $opt): ?>
                    <label>
                        <input type="radio" name="applicant[HouseOwnership]" value="<?= $opt ?>" <?= $a['HouseOwnership'] === $opt ? 'checked' : '' ?>>
                        <?= $opt ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="view-field">
                <label class="view-label">Gross Monthly Family Income</label>
                <select class="edit-input" name="applicant[GrossMonthlyFamilyIncome]">
                    <?php foreach (['Below 20000','20000-39999','40000-59999','60000+'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $a['GrossMonthlyFamilyIncome'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="view-section">
        <h3>Parents / Legal Guardians</h3>
        <?php foreach ($guardians as $i => $pg): ?>
        <div class="guardian-block">
            <input type="hidden" name="guardianID[<?= $i ?>]" value="<?= htmlspecialchars($pg['ParentGuardianID']) ?>">
            <p class="guardian-label">Guardian <?= $i + 1 ?></p>
            <div class="view-grid">
                <div class="view-field">
                    <label class="view-label">Name</label>
                    <input class="edit-input" type="text" name="parentguardian[ParentGuardianName][<?= $i ?>]" value="<?= htmlspecialchars($pg['ParentGuardianName']) ?>">
                </div>
                <div class="view-field">
                    <label class="view-label">Relationship</label>
                    <input class="edit-input" type="text" name="applicantguardian[Relationship][<?= $i ?>]" value="<?= htmlspecialchars($pg['Relationship']) ?>">
                </div>
                <div class="view-field">
                    <label class="view-label">Address</label>
                    <input class="edit-input" type="text" name="parentguardian[Address][<?= $i ?>]" value="<?= htmlspecialchars($pg['Address']) ?>">
                </div>
                <div class="view-field">
                    <label class="view-label">Occupation</label>
                    <input class="edit-input" type="text" name="parentguardian[Occupation][<?= $i ?>]" value="<?= htmlspecialchars($pg['Occupation']) ?>">
                </div>
                <div class="view-field">
                    <label class="view-label">Educational Attainment</label>
                    <input class="edit-input" type="text" name="parentguardian[EducationalAttainment][<?= $i ?>]" value="<?= htmlspecialchars($pg['EducationalAttainment']) ?>">
                </div>
                <div class="view-field">
                    <label class="view-label">Income Earner</label>
                    <div class="radio-group-edit">
                        <label><input type="radio" name="applicantguardian[IsIncomeEarner][<?= $i ?>]" value="1" <?= $pg['IsIncomeEarner'] ? 'checked' : '' ?>> Yes</label>
                        <label><input type="radio" name="applicantguardian[IsIncomeEarner][<?= $i ?>]" value="0" <?= !$pg['IsIncomeEarner'] ? 'checked' : '' ?>> No</label>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="modal-actions">
        <button type="submit" class="save-btn">Save Changes</button>
        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
    </div>
</form>