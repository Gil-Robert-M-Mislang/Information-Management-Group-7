<?php
include 'database.php';

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($id)) {
    echo "<p>Invalid applicant ID.</p>";
    exit();
}

// Fetch applicant with school names
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
?>

<h2 class="modal-title">Applicant Details</h2>

<div class="view-section">
    <h3>Personal Data</h3>
    <div class="view-grid">
        <div class="view-field"><span class="view-label">Applicant ID</span><span><?= htmlspecialchars($a['ApplicantID']) ?></span></div>
        <div class="view-field"><span class="view-label">Complete Name</span><span><?= htmlspecialchars($a['ApplicantName']) ?></span></div>
        <div class="view-field"><span class="view-label">Sex</span><span><?= htmlspecialchars($a['Sex']) ?></span></div>
        <div class="view-field"><span class="view-label">Civil Status</span><span><?= htmlspecialchars($a['CivilStatus']) ?></span></div>
        <div class="view-field"><span class="view-label">Citizenship</span><span><?= htmlspecialchars($a['Citizenship']) ?></span></div>
        <div class="view-field"><span class="view-label">Indigenous Group</span><span><?= htmlspecialchars($a['IndigenousGroup'] ?: 'N/A') ?></span></div>
        <div class="view-field"><span class="view-label">Date of Birth</span><span><?= htmlspecialchars($a['BirthDate']) ?></span></div>
        <div class="view-field"><span class="view-label">Place of Birth</span><span><?= htmlspecialchars($a['BirthPlace']) ?></span></div>
        <div class="view-field"><span class="view-label">Birth Order</span><span><?= htmlspecialchars($a['BirthOrder']) ?></span></div>
        <div class="view-field"><span class="view-label">Contact Number</span><span><?= htmlspecialchars($a['ContactNo']) ?></span></div>
        <div class="view-field"><span class="view-label">Email Address</span><span><?= htmlspecialchars($a['EmailAddress']) ?></span></div>
        <div class="view-field view-field-full"><span class="view-label">Permanent Address</span><span><?= htmlspecialchars($a['PermanentAddress']) ?></span></div>
    </div>
</div>

<div class="view-section">
    <h3>Educational Background</h3>
    <div class="view-grid">
        <div class="view-field"><span class="view-label">College</span><span><?= htmlspecialchars($a['CollegeName']) ?></span></div>
        <div class="view-field"><span class="view-label">College Address</span><span><?= htmlspecialchars($a['CollegeAddress']) ?></span></div>
        <div class="view-field"><span class="view-label">Course</span><span><?= htmlspecialchars($a['Course']) ?></span></div>
        <div class="view-field"><span class="view-label">Senior High School</span><span><?= htmlspecialchars($a['SHSName']) ?></span></div>
        <div class="view-field"><span class="view-label">SHS Address</span><span><?= htmlspecialchars($a['SHSAddress']) ?></span></div>
        <div class="view-field"><span class="view-label">SHS Strand</span><span><?= htmlspecialchars($a['SHSStrand']) ?></span></div>
        <div class="view-field"><span class="view-label">Junior High School</span><span><?= htmlspecialchars($a['JHSName']) ?></span></div>
        <div class="view-field"><span class="view-label">JHS Address</span><span><?= htmlspecialchars($a['JHSAddress']) ?></span></div>
        <div class="view-field"><span class="view-label">Elementary School</span><span><?= htmlspecialchars($a['ElemName']) ?></span></div>
        <div class="view-field"><span class="view-label">Elementary Address</span><span><?= htmlspecialchars($a['ElemAddress']) ?></span></div>
    </div>
</div>

<div class="view-section">
    <h3>Socio-Economic Background</h3>
    <div class="view-grid">
        <div class="view-field"><span class="view-label">House Ownership</span><span><?= htmlspecialchars($a['HouseOwnership']) ?></span></div>
        <div class="view-field"><span class="view-label">Gross Monthly Family Income</span><span><?= htmlspecialchars($a['GrossMonthlyFamilyIncome']) ?></span></div>
    </div>
</div>

<div class="view-section">
    <h3>Parents / Legal Guardians</h3>
    <?php
    $pgIndex = 1;
    while ($pg = mysqli_fetch_assoc($guardianQuery)):
    ?>
    <div class="guardian-block">
        <p class="guardian-label">Guardian <?= $pgIndex ?></p>
        <div class="view-grid">
            <div class="view-field"><span class="view-label">Name</span><span><?= htmlspecialchars($pg['ParentGuardianName']) ?></span></div>
            <div class="view-field"><span class="view-label">Relationship</span><span><?= htmlspecialchars($pg['Relationship']) ?></span></div>
            <div class="view-field"><span class="view-label">Address</span><span><?= htmlspecialchars($pg['Address']) ?></span></div>
            <div class="view-field"><span class="view-label">Occupation</span><span><?= htmlspecialchars($pg['Occupation']) ?></span></div>
            <div class="view-field"><span class="view-label">Educational Attainment</span><span><?= htmlspecialchars($pg['EducationalAttainment']) ?></span></div>
            <div class="view-field"><span class="view-label">Income Earner</span><span><?= $pg['IsIncomeEarner'] ? 'Yes' : 'No' ?></span></div>
        </div>
    </div>
    <?php $pgIndex++; endwhile; ?>
</div>