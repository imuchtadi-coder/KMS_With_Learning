<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: 'Georgia', serif; width: 297mm; height: 210mm; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; }
        .certificate { width: 280mm; height: 190mm; background: white; border: 3px solid #d4af37; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); position: relative; text-align: center; }
        .certificate::before { content: ''; position: absolute; top: 10px; left: 10px; right: 10px; bottom: 10px; border: 1px solid #d4af37; pointer-events: none; }
        .content { position: relative; z-index: 1; }
        .header { margin-bottom: 30px; }
        .seal { width: 60px; height: 60px; background: #d4af37; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; font-size: 12px; }
        h1 { font-size: 48px; color: #333; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 3px; }
        .subtitle { font-size: 18px; color: #666; font-style: italic; margin-bottom: 40px; }
        .body { margin: 40px 0; line-height: 1.8; }
        .body p { font-size: 14px; color: #555; margin: 10px 0; }
        .awarded-to { font-size: 16px; color: #333; margin: 30px 0 10px 0; }
        .student-name { font-size: 32px; color: #667eea; font-weight: bold; margin: 20px 0 30px 0; text-decoration: underline; text-decoration-thickness: 2px; text-underline-offset: 5px; }
        .course-name { font-size: 18px; color: #666; margin: 10px 0; font-style: italic; }
        .footer { display: flex; justify-content: space-around; margin-top: 50px; padding-top: 30px; border-top: 1px solid #ddd; }
        .signature { width: 150px; text-align: center; }
        .signature-line { border-top: 1px solid #333; margin: 40px 0 5px 0; padding-top: 5px; font-size: 12px; color: #333; }
        .date { font-size: 12px; color: #666; margin-top: 10px; }
        .certificate-number { position: absolute; bottom: 20px; right: 30px; font-size: 11px; color: #999; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="content">
            <div class="header">
                <div class="seal">✓</div>
                <h1>Certificate</h1>
                <p class="subtitle">of Completion</p>
            </div>
            <div class="body">
                <p class="awarded-to">This is proudly presented to</p>
                <p class="student-name"><?= $user->name ?></p>
                <p>for successfully completing the course</p>
                <p class="course-name"><?= $course->title ?></p>
                <p style="margin-top: 30px;">
                    As recognition of dedicated effort and commitment to learning,
                    this certificate is awarded on <?= $issued_date ?>.
                </p>
            </div>
            <div class="footer">
                <div class="signature">
                    <div class="signature-line">Course Instructor</div>
                </div>
                <div class="signature">
                    <div class="signature-line"><?= now()->format('F d, Y') ?></div>
                    <div class="date">Date</div>
                </div>
            </div>
            <div class="certificate-number">
                <?= $certificate->certificate_number ?>
            </div>
        </div>
    </div>
</body>
</html>
