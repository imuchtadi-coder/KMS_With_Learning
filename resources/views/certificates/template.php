<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 297mm;
            height: 210mm;
        }

        body {
            font-family: Georgia, "Times New Roman", serif;
            background: #f4f1e8;
            color: #1d2f4f;
        }

        .certificate {
            position: relative;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            background: #faf9f4;
        }

        /*
        |--------------------------------------------------------------------------
        | OUTER BORDER
        |--------------------------------------------------------------------------
        */

        .outer-border {
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;

            border: 1.2mm solid #172f52;
        }

        .gold-border {
            position: absolute;
            top: 11mm;
            left: 11mm;
            right: 11mm;
            bottom: 11mm;

            border: 0.45mm solid #c9a44c;
        }

        .inner-border {
            position: absolute;
            top: 14mm;
            left: 14mm;
            right: 14mm;
            bottom: 14mm;

            border: 0.25mm solid #d9c27a;
        }


        /*
        |--------------------------------------------------------------------------
        | NAVY CORNER DECORATION
        |--------------------------------------------------------------------------
        */

        /* .corner-top-left {
            position: absolute;
            top: 0;
            left: 0;

            width: 95mm;
            height: 58mm;

            background: #172f52;

            border-bottom-right-radius: 80mm;
        }

        .corner-top-left-gold {
            position: absolute;
            top: 0;
            left: 0;

            width: 77mm;
            height: 45mm;

            background: #c9a44c;

            border-bottom-right-radius: 65mm;
        }

        .corner-top-left-inner {
            position: absolute;
            top: 0;
            left: 0;

            width: 67mm;
            height: 38mm;

            background: #faf9f4;

            border-bottom-right-radius: 55mm;
        }


        .corner-bottom-right {
            position: absolute;
            right: 0;
            bottom: 0;

            width: 100mm;
            height: 62mm;

            background: #172f52;

            border-top-left-radius: 90mm;
        }

        .corner-bottom-right-gold {
            position: absolute;
            right: 0;
            bottom: 0;

            width: 82mm;
            height: 48mm;

            background: #c9a44c;

            border-top-left-radius: 72mm;
        }

        .corner-bottom-right-inner {
            position: absolute;
            right: 0;
            bottom: 0;

            width: 71mm;
            height: 40mm;

            background: #faf9f4;

            border-top-left-radius: 62mm;
        } */


        /*
        |--------------------------------------------------------------------------
        | TOP BRAND
        |--------------------------------------------------------------------------
        */

        .brand {
            position: absolute;
            top: 18mm;
            left: 0;
            right: 0;

            text-align: center;

            z-index: 10;
        }

        .brand-name {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 4px;
            color: #172f52;
            text-transform: uppercase;
        }

        .brand-line {
            width: 38mm;
            margin: 2mm auto 0;

            border-top: 0.6mm solid #c9a44c;
        }


        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        .title-section {
            position: absolute;
            top: 48mm;
            left: 30mm;
            right: 30mm;

            text-align: center;

            z-index: 10;
        }

        .title {
            font-size: 31px;
            font-weight: bold;
            letter-spacing: 3px;
            color: #172f52;
            text-transform: uppercase;
        }

        .subtitle {
            margin-top: 2mm;

            font-size: 15px;
            color: #b08b32;

            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .title-divider {
            width: 45mm;
            margin: 4mm auto 0;

            border-top: 0.4mm solid #c9a44c;
        }


        /*
        |--------------------------------------------------------------------------
        | MAIN CONTENT
        |--------------------------------------------------------------------------
        */

        .content {
            position: absolute;
            top: 76mm;
            left: 35mm;
            right: 35mm;

            text-align: center;

            z-index: 10;
        }

        .presented {
            font-size: 13px;
            color: #666b73;

            letter-spacing: 1px;
        }

        .student-name {
            margin-top: 5mm;

            font-family: Georgia, "Times New Roman", serif;
            font-size: 34px;
            font-weight: bold;

            color: #b08b32;

            letter-spacing: 1px;
        }

        .name-line {
            width: 105mm;
            margin: 2mm auto 5mm;

            border-top: 0.35mm solid #c9a44c;
        }

        .completion-text {
            font-size: 13px;
            color: #555b63;

            line-height: 1.6;
        }

        .course-name {
            width: 175mm;

            margin: 4mm auto 0;

            font-size: 21px;
            font-weight: bold;

            color: #172f52;

            line-height: 1.35;
        }

        .recognition {
            width: 160mm;

            margin: 5mm auto 0;

            font-size: 11px;
            line-height: 1.6;

            color: #70757b;
        }


        /*
        |--------------------------------------------------------------------------
        | BADGE
        |--------------------------------------------------------------------------
        */

        .badge {
            position: absolute;

            top: 23mm;
            right: 27mm;

            width: 30mm;
            height: 30mm;

            border: 1mm solid #c9a44c;
            border-radius: 50%;

            background: #172f52;

            text-align: center;

            z-index: 20;
        }

        .badge-inner {
            position: absolute;

            top: 2.5mm;
            left: 2.5mm;

            width: 23mm;
            height: 23mm;

            border: 0.35mm solid #e2c76f;
            border-radius: 50%;
        }

        .badge-check {
            margin-top: 4mm;

            font-size: 16px;
            font-weight: bold;

            color: #e2c76f;
        }

        .badge-text {
            margin-top: 1mm;

            font-family: Arial, sans-serif;
            font-size: 6px;

            letter-spacing: 1px;
            color: #ffffff;

            text-transform: uppercase;
        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer {
            position: absolute;

            left: 30mm;
            right: 30mm;
            bottom: 25mm;

            z-index: 10;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-left {
            width: 35%;
            text-align: left;
            vertical-align: bottom;
        }

        .footer-center {
            width: 30%;
            text-align: center;
            vertical-align: bottom;
        }

        .footer-right {
            width: 35%;
            text-align: right;
            vertical-align: bottom;
        }

        .footer-label {
            font-family: Arial, sans-serif;
            font-size: 8px;

            color: #7b7f84;

            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-value {
            margin-top: 1.5mm;

            font-family: Arial, sans-serif;
            font-size: 9px;

            color: #172f52;
        }

        .signature-line {
            width: 45mm;

            margin-bottom: 2mm;

            border-top: 0.4mm solid #172f52;
        }

        .footer-center .signature-line {
            margin-left: auto;
            margin-right: auto;
        }

        .certificate-number {
            font-family: Arial, sans-serif;
            font-size: 8px;

            color: #777;

            letter-spacing: 0.5px;
        }

        .date-value {
            font-family: Arial, sans-serif;
            font-size: 9px;

            color: #172f52;
        }


        /*
        |--------------------------------------------------------------------------
        | DECORATIVE LINES
        |--------------------------------------------------------------------------
        */

        .decorative-line-top {
            position: absolute;

            top: 17mm;
            left: 105mm;

            width: 87mm;

            border-top: 0.25mm solid #d7c47f;

            z-index: 5;
        }

        .decorative-line-bottom {
            position: absolute;

            bottom: 18mm;
            left: 105mm;

            width: 87mm;

            border-top: 0.25mm solid #d7c47f;

            z-index: 5;
        }

        .certificate-logo {
            display: block;
            margin: 0 auto;
        }

    </style>
</head>

<body>

<div class="certificate">

    <!-- =========================================================
         BORDER
    ========================================================== -->

    <div class="outer-border"></div>
    <div class="gold-border"></div>
    <div class="inner-border"></div>


    <!-- =========================================================
         DECORATIVE CORNERS
    ========================================================== -->

    <div class="corner-top-left"></div>
    <div class="corner-top-left-gold"></div>
    <div class="corner-top-left-inner"></div>

    <div class="corner-bottom-right"></div>
    <div class="corner-bottom-right-gold"></div>
    <div class="corner-bottom-right-inner"></div>


    <!-- =========================================================
         DECORATIVE LINES
    ========================================================== -->

    <div class="decorative-line-top"></div>
    <div class="decorative-line-bottom"></div>


    <!-- =========================================================
         KIT SULUH BRAND
    ========================================================== -->

    <div class="brand">

        <?php if (!empty($certificate_logo)): ?>

            <img
                src="file://<?= htmlspecialchars($certificate_logo) ?>"
                class="certificate-logo"
                style="
                    width: <?= $certificate_logo_width ?>mm;
                    height: <?= $certificate_logo_height ?>mm;
                "
            >

        <?php elseif (!empty($tenant?->name)): ?>

            <div class="brand-name">
                <?= htmlspecialchars($tenant->name) ?>
            </div>

        <?php endif; ?>

        <div class="brand-line"></div>

    </div>


    <!-- =========================================================
         BADGE
    ========================================================== -->

    <div class="badge">

        <div class="badge-inner"></div>

        <div class="badge-check">
            ✓
        </div>

        <div class="badge-text">
            KITSuluh<br>
            Certified
        </div>

    </div>


    <!-- =========================================================
         TITLE
    ========================================================== -->

    <div class="title-section">

        <div class="title">
            Certificate
        </div>

        <div class="subtitle">
            of Completion
        </div>

        <div class="title-divider"></div>

    </div>


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <div class="content">

        <div class="presented">
            This certificate is proudly presented to
        </div>


        <div class="student-name">
            <?= htmlspecialchars($user->name) ?>
        </div>

        <div class="name-line"></div>


        <div class="completion-text">
            for successfully completing the course
        </div>


        <div class="course-name">
            <?= htmlspecialchars($course->title) ?>
        </div>


        <div class="recognition">
            This certificate recognizes the successful completion of the
            learning requirements for the course and is awarded in
            recognition of the recipient's commitment to learning.
        </div>

    </div>


    <!-- =========================================================
         FOOTER
    ========================================================== -->

    <div class="footer">

        <table class="footer-table">

            <tr>

                <!-- Certificate Number -->
                <td class="footer-left">

                    <div class="certificate-number">
                        Certificate No.
                    </div>

                    <div class="footer-value">
                        <?= htmlspecialchars($certificate->certificate_number) ?>
                    </div>

                </td>


                <!-- Date -->
                <td class="footer-center">

                    <div class="signature-line"></div>

                    <div class="date-value">
                        <?= htmlspecialchars($issued_date) ?>
                    </div>

                    <div class="footer-label">
                        Date Issued
                    </div>

                </td>


                <!-- Instructor -->
                <td class="footer-right">

                    <div class="signature-line" style="margin-left:auto;"></div>

                    <div class="footer-value">
                        Course Instructor
                    </div>

                    <div class="footer-label">
                        KITSuluh Learning Platform
                    </div>

                </td>

            </tr>

        </table>

    </div>

</div>

</body>
</html>