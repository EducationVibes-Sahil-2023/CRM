<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>David Tvildiani Medical University</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">


    <?php

    // $path = $university_data["banner_image"] ?? '';
    // $type = pathinfo($path, PATHINFO_EXTENSION);
    // $data = file_get_contents($path);
    // $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

    $university_data = !empty($university_data)
        ? json_decode($university_data, true)
        : [];
    $contactInfo = !empty($contact_data)
        ? json_decode($contact_data, true)
        : [];

    $countryName = $university_data["country_name"];
    $universityName = $university_data["university_name"];
    $duration = $university_data["duration"];
    $founded_year = $university_data["founded_year"];
    $logo =  getBase64Image($university_data["logo"] ? $university_data["logo"] : 'https://educationvibes.in/assets/new_images/logo.webp');
    $university_logo =  getBase64Image($university_data["university_logo"] ?? '');
    $university_banner = getBase64Image($university_data["banner_image"] ?? '');
    $sectionData  = !empty($section_data)
        ? json_decode($section_data, true)
        : [];

    $feesDetails = $sectionData["fees"] ?? [];
    $otherchargeDetails =  $sectionData["other_charges"] ?? [];
    $one_time_charges = $sectionData["one_time_charges"] ?? [];
    $our_services = $sectionData["services"] ?? [];
    $processing_fee = $sectionData["processing_fee"] ?? [];

    ?>

    <style>
        :root {
            --primary-gradient: linear-gradient(90deg, #136db9, #15a3ae);
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f3f6f8;

        }

        /* MAIN CONTAINER */
        .wrapper {
            background: #fff;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: auto;
            margin: 50px auto;
        }

        /* HERO IMAGE */
        .hero {
            position: relative;
            text-align: center;
            width: 96%;
            margin: 2%;
            border-radius: 20px;
            /* overflow: hidden; */
        }

        .hero img#university_banner {
            margin: 0px !important;
            width: 100%;
            height: 380px;
            object-fit: cover;
            min-height: 380px;
            border-radius: 20px;
        }

        .logo-container {
            height: 100px;
            width: 150px;
            position: absolute;
            right: 20px;
            top: -40px;
            padding: 10px;
            border-radius: 20px;
            transform: rotate(10deg);
            background: white;
        }

        img#logo {
            object-fit: contain;
            height: 80%;
            min-height: auto;
            width: 100%;
            padding-top: 30px;
            transform: rotate(-10deg);
        }




        .top-info-container {
            position: absolute;
            padding: 20px;
            bottom: -44px;
            left: 0px;
            background: #fff;
            border-radius: 0px 20px;
        }

        /* TOP INFO BOX */
        .top-info {

            background: #fff;
            border: 2px solid #e63946;
            padding: 20px 30px;
            border-radius: 15px;
            font-weight: 600;
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .top-info span {
            color: #e63946;
        }

        /* 6 YEAR BADGE */
        .badge-year {
            position: absolute;
            right: 40px;
            bottom: -30px;
            background: #2aa7c9;
            color: #fff;
            padding: 15px 35px;
            border-radius: 15px;
            font-size: 22px;
            font-weight: 600;
        }

        /* TITLE */
        .title {
            text-align: left;
            padding: 20px 35px 15px;
            font-size: 50px;
            font-weight: 600;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
            /* or 700 */
        }

        /* TABLE */
        .fee-table {
            width: 95%;
            margin: 0 auto;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 12px;
            min-height: 300px;
        }

        .fee-table thead {
            background: var(--primary-gradient);
            color: #fff;
        }

        .fee-table th,
        .fee-table td {
            padding: 10px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            border-bottom: 1px solid #ddd;
            border-right: 1px solid #ddd;
            border-left: 1px solid #ddd;
        }

        .fee-table tbody tr:nth-child(even) {
            background: #f2f2f2;
        }

        .fee-table tfoot {
            background: var(--primary-gradient);
            color: #fff;
            font-weight: 600;
        }

        /* OTHER CHARGES */
        .other-charges {
            width: 95%;
            margin: 25px auto;
            border-bottom: 2px solid #2aa7c9;
            border-left: 2px solid #2aa7c9;
            font-weight: 600;
            border-right: 2px solid #2aa7c9;
            border-radius: 15px;
            /* overflow: hidden; */
            /* min-height: 80px; */
            max-height: 200px;
            overflow: clip;
        }

        .other-charges table {
            width: 100%;
            border-collapse: collapse;
        }

        .other-charges td {
            padding: 10px;
            font-size: 14px;
            text-align: left;
            border: 2px solid #2aa7c9;
            font-weight: 600;
        }

        .other-title {
            color: #2aa7c9;
            font-weight: 500;
            font-size: 20px;
            text-align: center !important;
        }

        /* CARDS */
        .card-section {
            width: 95%;
            margin: 10px auto;
            display: flex;
            gap: 30px;
            min-height: 300px;
        }

        .card {
            /* flex: 1; */
            border: 2px solid #2aa7c9;
            border-radius: 20px;
            padding: 25px;
            position: relative;

        }

        /* Flex distribution */
        .card:nth-child(1) {
            flex: 0 0 25%;
        }

        .card:nth-child(2) {
            flex: 0 0 25%;
        }

        .card:nth-child(3) {
            flex: 0 0 30%;
        }

        .card h3 {
            width: 200px;
            position: absolute;
            top: -18px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-gradient);
            color: #fff;
            padding: 8px 25px;
            border-radius: 0px 0px 10px 10px;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }

        .card ul {
            margin-top: 20px;
            padding-left: 5px;
            font-size: 14px;
            line-height: 20px;

        }

        .card ul li::marker {
            color: #2aa7c9;
            /* Teal Blue */
        }

        /* FOOTER */
        .footer {
            background: #111;
            color: #fff;
            /* padding: 15px 40px; */
        }

        .footer-top {
            background: #e63946;
            padding: 8px 20px;
            font-weight: 600;
        }

        .phone {
            float: right;
            color: #2aa7c9;
            font-weight: 600;
        }

        .processing-table {
            margin-top: 20px;
            font-size: 14px;
        }

        .processing-table thead tr th,
        .processing-table tfoot tr td {
            font-size: 16px !important;
            text-align: left;
            font-weight: bold;
            text-wrap: none;
            color: var(--primary-gradient);

        }

        .processing-table tbody tr td {
            padding: 5px 0px;
            border-bottom: 2px solid #2aa7c9;
        }

        .gst-title {
            background: var(--primary-gradient);
            color: white;
            padding: 10px 30px;
            display: inline-block;
            font-weight: 600;
            border-radius: 10px;

        }

        .custom-footer {
            position: relative !important;

            bottom: 0px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to right, #2b2f36, #1f242b);
            color: #fff;
            overflow: hidden;
            padding: 10px 20px;
        }

        /* Left Side */
        .footer-left {
            position: relative;
        }

        .head-office {
            display: inline-block !important;
            background: linear-gradient(90deg, #ff2d3b, #d81b2a);
            padding: 12px 25px;
            font-size: 20px;
            font-weight: 600;
            display: inline-block;
            clip-path: polygon(0 0, 95% 0, 85% 100%, 0% 100%);
            color: white;
            position: relative;
            width: 200px;
            z-index: 9999;
            bottom: -18px;
            padding: 8px 20px;

        }

        .branches {
            margin-top: 10px;
            font-size: 16px;
        }

        .branch-title {
            color: #e6f5b8;
            font-weight: bold;
            margin-right: 10px;
        }

        /* Right Side Phone Box */
        .footer-right {
            position: relative;
        }

        .phone-box {
            width: 250px;
            text-align: center;
            background: var(--primary-gradient);
            padding: 20px 20px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 5px;
            clip-path: polygon(15% 0, 100% 0, 100% 100%, 0 100%);
            /* display: flex; */
            align-items: center;
            gap: 15px;
        }

        .phone-box i {
            font-size: 28px;
        }

        #feeStructures {
            background: white;
            width: 1100px;
            height: 1523px;
            /* A4 height */
            display: flex;
            flex-direction: column;
        }

        #university_logo {
            height: 50px;
            max-width: 100px;
        }

        .header-layout {
            height: 40px;
            position: absolute;
            width: 102%;
            top: -25px;
            z-index: 99;
            right: 5px;
        }

        .header-layout img {
            width: 103%;
            height: 100%;
            object-fit: cover;
        }

        .left-side-layout {
            background: var(--primary-gradient);
            height: 200px;
            width: 6px;
            border-radius: 0px 20px 20px 0px;
            position: absolute;
            object-fit: contain;
            left: 210px;
            top: 430px;
        }


        .left-side-layout-lower {
            background: var(--primary-gradient);
            height: 200px;
            width: 6px;
            border-radius: 0px 20px 20px 0px;
            position: absolute;
            object-fit: contain;
            left: 210px;
            top: 1030px;
        }


        .right-side-layout {
            background: var(--primary-gradient);
            height: 200px;
            width: 6px;
            border-radius: 20px 0px 0px 20px;
            position: absolute;
            object-fit: contain;
            right: 210px;
            top: 430px;
        }


        .right-side-layout-lower {
            background: var(--primary-gradient);
            height: 200px;
            width: 6px;
            border-radius: 20px 0px 0px 20px;
            position: absolute;
            object-fit: contain;
            right: 210px;
            top: 1030px;
        }
    </style>
</head>

<body <?php if (!empty($_REQUEST['download']) && $_REQUEST['download'] == 1): ?> onload="generatePDFAndUpload()" <?php endif; ?>>

    <div class="wrapper" id="feeStructures">
        <!-- HERO -->
        <div class="hero" style="text-align: center;">
            <div class="header-layout">
                <img src="<?= base_url('/assets/pdf_layout/header-layout.png') ?>">
            </div>

            <?php if (!empty($logo)) { ?>
                <div class="logo-container"
                    style="background-image: url('<?= base_url('assets/pdf_layout/logo-layout.png') ?>');">

                    <img id="logo" src="<?= $logo ?>" class="w-100 h-100">

                </div>
            <?php } ?>
            <img id="university_banner" src="<?= $university_banner ?>">
            <div class="top-info-container">
                <div class="top-info">
                    <?php if (!empty($university_logo)) { ?>

                        <img id="university_logo" src="<?= $university_logo ?>" class="">

                    <?php } ?>
                    <div><span>Establishment:</span> <?= $founded_year ?>
                    </div>
                    <div><span>Country:</span> <?= $duration ?></div>
                </div>
            </div>
            <div class="badge-year"><?= $duration ?></div>
        </div>

        <div class="left-side-layout"></div>
        <div class="left-side-layout-lower"></div>
        <div class="right-side-layout"></div>
        <div class="right-side-layout-lower"></div>

        <!-- TITLE -->
        <div class="title">

            <?= $universityName ?>
        </div>

        <!-- TABLE -->
        <?php if (!empty($feesDetails)) : ?>

            <table class="fee-table">

                <!-- THEAD -->
                <thead>
                    <tr>
                        <?php foreach ($feesDetails['header'] as $head) : ?>
                            <th><?= htmlspecialchars($head) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <!-- TBODY -->
                <tbody>
                    <?php foreach ($feesDetails['data'] as $row) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['year']) ?></td>
                            <td>
                                <?= $row['tuition'] == 0 ? 'Optional' : number_format($row['tuition']) ?>
                            </td>
                            <td>
                                <?= $row['hostel'] == 0 ? 'Optional' : number_format($row['hostel']) ?>
                            </td>
                            <td>
                                <?= $row['development'] == 0 ? 'Optional' : number_format($row['development']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

                <!-- TFOOT -->
                <?php if (!empty($feesDetails['footer'])) : ?>
                    <tfoot>
                        <?php foreach ($feesDetails['footer'] as $foot) : ?>
                            <tr>
                                <td><?= htmlspecialchars($foot['label']) ?></td>
                                <td><?= number_format($foot['tuition']) ?></td>
                                <td><?= number_format($foot['hostel']) ?></td>
                                <td><?= number_format($foot['development']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tfoot>
                <?php endif; ?>

            </table>

        <?php endif; ?>


        <?php if (!empty($otherchargeDetails['data'])) : ?>

            <div class="other-charges">
                <table>

                    <?php
                    $items = $otherchargeDetails['data'] ?? [];
                    $items = array_values(array_filter($items)); // remove empty values
                    $totalItems = count($items);
                    ?>

                    <?php if ($totalItems > 0): ?>

                        <?php
                        $rows = ceil($totalItems / 2);
                        ?>

                        <?php for ($i = 0; $i < $rows; $i++): ?>
                            <tr>

                                <?php if ($i === 0): ?>
                                    <td class="other-title" rowspan="<?= $rows ?>">
                                        <h3><?= htmlspecialchars($otherchargeDetails['title'] ?? '') ?></h3>
                                    </td>
                                <?php endif; ?>

                                <?php if (isset($items[$i * 2])): ?>
                                    <td><?= htmlspecialchars($items[$i * 2]) ?></td>
                                <?php endif; ?>

                                <?php if (isset($items[$i * 2 + 1])): ?>
                                    <td><?= htmlspecialchars($items[$i * 2 + 1]) ?></td>
                                <?php endif; ?>

                            </tr>
                        <?php endfor; ?>

                    <?php endif; ?>

                </table>
            </div>



        <?php endif; ?>

        <!-- CARDS -->
        <div class="card-section">
            <?php if (!empty($one_time_charges['items'])) : ?>

                <div class="card">
                    <h3><?= htmlspecialchars($one_time_charges['title']) ?></h3>

                    <ul>
                        <?php foreach ($one_time_charges['items'] as $item) : ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            <?php endif; ?>


            <?php if (!empty($our_services['items'])) : ?>

                <div class="card">
                    <h3><?= htmlspecialchars($our_services['title']) ?></h3>

                    <ul>
                        <?php foreach ($our_services['items'] as $item) : ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            <?php endif; ?>


            <?php if (!empty($processing_fee['data'])) : ?>

                <div class="card">
                    <h3><?= htmlspecialchars($processing_fee['title']) ?></h3>

                    <table class="processing-table" width="100%">
                        <colgroup>
                            <col style="width:58%">
                            <col style="width:40%">
                        </colgroup>

                        <thead>
                            <?php foreach ($processing_fee['header'] as $h_item) : ?>
                                <tr>
                                    <th><?= htmlspecialchars($h_item['description']) ?? 'Installment' ?></th>
                                    <th><?= htmlspecialchars($h_item['amount']) ?? 'Amount (INR)' ?></th>
                                </tr>
                            <?php endforeach; ?>
                        </thead>

                        <tbody>
                            <?php foreach ($processing_fee['data'] as $k => $item) : ?>
                                <tr>
                                    <td><?= ($k + 1) . ". " . htmlspecialchars($item['description']) ?></td>
                                    <td><?= number_format((float)$item['amount']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <?php foreach ($processing_fee['footer'] as $f_footer) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($f_footer['description']) ?? 'Total' ?></td>
                                    <td><?= number_format($f_footer['amount']) ?? '0' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tfoot>
                    </table>

                    <?php if (!empty($processing_fee['gst_text'])) : ?>
                        <div style="text-align: center;">
                            <h4 class="gst-title">
                                *<?= htmlspecialchars($processing_fee['gst_text']) ?>
                            </h4>
                        </div>
                    <?php endif; ?>

                </div>

            <?php endif; ?>
        </div>
        <div class="row">
            <div class="head-office">
                Head Office : Pune
            </div>
            <footer class="custom-footer">
                <div class="footer-left">
                    <div class="branches">
                        <span class="branch-title">Branches:</span>
                        <?= $locations ?? 'Noida | Indore | Patna | Latur | Jalgaon | Nagpur |
                        Mumbai | Hyderabad' ?>
                    </div>
                </div>

                <div class="footer-right">
                    <div class="phone-box">
                        <i class="fa fa-phone"></i>
                        <span><?= !empty($contactInfo["phone"]) ? $contactInfo["phone"] : '+91 7217219100' ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

</body>

</html>

<script>
    // window.onload = function() {
    //     setTimeout(function() {
    //         generatePDF();
    //     }, 1000);
    // };



    async function generatePDFAndUpload() {
        const {
            jsPDF
        } = window.jspdf;

        const element = document.getElementById("feeStructures");
        if (!element) {
            alert("Element not found");
            return;
        }

        // Capture the element as canvas
        const canvas = await html2canvas(element, {
            scale: 2,
            useCORS: true,
            allowTaint: false,
            backgroundColor: "#ffffff"
        });

        // Convert canvas to image
        const imgData = canvas.toDataURL("image/jpeg", 1.2);

        // Create PDF
        const pdf = new jsPDF("p", "mm", "a4");
        const imgWidth = 210;
        const pageHeight = 297;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        if (imgHeight <= pageHeight) {
            pdf.addImage(imgData, "JPEG", 0, 0, imgWidth, imgHeight);
        } else {
            let heightLeft = imgHeight;
            let position = 0;

            pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            while (heightLeft > 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }
        }

        // Convert PDF to Blob
        const pdfBlob = pdf.output("blob");

        // Prepare FormData
        const formData = new FormData();
        const filename = "<?= str_replace(' ', '_', $universityName) ?>.pdf";
        formData.append("pdf_file", pdfBlob, filename);

        // Correct CSRF token append
        formData.append("csrf_token_name", "77a5427eaef71e10ee2dbb92c5cc00f1");
        formData.append("country_name", "<?= $university_data['country_name'] ?>");
        formData.append("university_name", "<?= $university_data['university_name'] ?>");
        formData.append("segment_type", "<?= $university_data['segment_type'] ?>");

        // Send to server
        try {
            const response = await fetch("<?= base_url('admin/Fees/savePdf') ?>", {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            });

            const result = await response.text();
            console.log("Server response:", result);
        } catch (error) {
            console.error("Upload failed:", error);
        }
    }
</script>