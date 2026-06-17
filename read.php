<?php
    include 'database.php';

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['searchInput']) ? $_GET['searchInput'] : '';
    $sort = isset($_GET['sortSelect']) ? $_GET['sortSelect'] : 'id';

    $limit = 10;
    $offset = ($page - 1) * $limit;
    $orderBy = " ";

    if ($sort === 'name') {
        $orderBy = "ORDER BY ApplicantName";
    } elseif ($sort === 'contactNumber') {
        $orderBy = "ORDER BY ContactNo";
    } elseif ($sort === 'email') {
        $orderBy = "ORDER BY EmailAddress";
    } else {
        $orderBy = "ORDER BY REGEXP_REPLACE(ApplicantID, '[0-9]', ''), CAST(REGEXP_REPLACE(ApplicantID, '[^0-9]', '') AS UNSIGNED)";
    }

    $whereCondition = "";

    if (!empty($search)) {

        $search = mysqli_real_escape_string($conn, $search);

        $whereCondition =
            "WHERE
        ApplicantID LIKE '%$search%'
        OR ApplicantName LIKE '%$search%'
        OR ContactNo LIKE '%$search%'
        OR EmailAddress LIKE '%$search%'";
    }


    $query = "SELECT * FROM applicant_t $whereCondition $orderBy LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $query);


    if ($result && mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_assoc($result)) {
    ?>

            <tr>
                <td><?php echo $row['ApplicantID']; ?></td>
                <td><?php echo $row['ApplicantName']; ?></td>
                <td><?php echo $row['ContactNo']; ?></td>
                <td><?php echo $row['EmailAddress']; ?></td>

                <td class="actions-cell">
                    <div class="actions-wrapper">
                        <button class="view-btn" data-id="<?php echo $row['ApplicantID']; ?>">View</button>
                        <button class="edit-btn" data-id="<?php echo $row['ApplicantID']; ?>">Edit</button>
                        <button class="delete-btn" data-id="<?php echo $row['ApplicantID']; ?>">Delete</button>
                    </div>
                </td>
            </tr>

        <?php
        }
    } else {
        ?>

        <tr class="no-records">
            <td colspan="5" style="text-align:center;">
                No Records Found
            </td>
        </tr>

    <?php
    }
?>