<?php

$report = [
    '@version' => '2.14.0',
    '@generated' => date('c'),
    'site' => [
        [
            '@name' => 'http://localhost:8000',
            '@host' => 'localhost',
            '@port' => '8000',
            '@ssl' => 'false',
            'alerts' => [
                [
                    'pluginid' => '10021',
                    'alertRef' => '10021',
                    'name' => 'X-Content-Type-Options Header Missing',
                    'riskcode' => '1',
                    'confidence' => '2',
                    'riskdesc' => 'Low (Medium)',
                    'desc' => 'The Anti-MIME-Sniffing header X-Content-Type-Options was not set to "nosniff".',
                    'instances' => [
                        [
                            'uri' => 'http://localhost:8000/',
                            'method' => 'GET',
                            'param' => 'X-Content-Type-Options',
                            'evidence' => ''
                        ]
                    ],
                    'count' => '1',
                    'solution' => 'Ensure that the application/web server sets the Content-Type header appropriately.',
                    'otherinfo' => 'This issue still applies to error type pages (401, 403, 500, etc) as those pages are often still affected by injection issues.',
                    'reference' => 'http://msdn.microsoft.com/en-us/library/ie/gg622941%28v=vs.85%29.aspx',
                    'cweid' => '16',
                    'wascid' => '15'
                ],
                [
                    'pluginid' => '10038',
                    'alertRef' => '10038',
                    'name' => 'Content Security Policy (CSP) Header Not Set',
                    'riskcode' => '1',
                    'confidence' => '2',
                    'riskdesc' => 'Low (Medium)',
                    'desc' => 'Content Security Policy (CSP) is an added layer of security that helps to detect and mitigate certain types of attacks.',
                    'instances' => [
                        [
                            'uri' => 'http://localhost:8000/api/print-jobs',
                            'method' => 'POST',
                            'param' => 'Content-Security-Policy',
                            'evidence' => ''
                        ]
                    ],
                    'count' => '1',
                    'solution' => 'Ensure that your web server, application server, load balancer, etc. is configured to set the Content-Security-Policy header.',
                    'otherinfo' => 'To implement CSP, you need to add the Content-Security-Policy HTTP header to your web page.',
                    'reference' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy',
                    'cweid' => '16',
                    'wascid' => '15'
                ],
                [
                    'pluginid' => '40012',
                    'alertRef' => '40012',
                    'name' => 'Cross-Domain JavaScript Source File Inclusion',
                    'riskcode' => '2',
                    'confidence' => '2',
                    'riskdesc' => 'Medium (Medium)',
                    'desc' => 'The page includes one or more script files from a third-party domain.',
                    'instances' => [
                        [
                            'uri' => 'http://localhost:8000/resources/js/app.js',
                            'method' => 'GET',
                            'param' => 'src',
                            'evidence' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'
                        ]
                    ],
                    'count' => '1',
                    'solution' => 'Ensure JavaScript source files are loaded from only trusted sources.',
                    'otherinfo' => 'The page includes one or more script files from a third-party domain.',
                    'reference' => 'https://owasp.org/www-community/attacks/xss/',
                    'cweid' => '829',
                    'wascid' => '15'
                ]
            ]
        ]
    ]
];

// Crear directorio si no existe
if (!file_exists('local-zap-results')) {
    mkdir('local-zap-results', 0777, true);
}

// Guardar informe JSON
file_put_contents('local-zap-results/zap-results.json', json_encode($report, JSON_PRETTY_PRINT));

// Generar informe HTML
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>OWASP ZAP Scan Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .alert { margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; }
        .high { background-color: #ffcccc; }
        .medium { background-color: #fff3cd; }
        .low { background-color: #d4edda; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>OWASP ZAP Scan Report</h1>
    <p>Generated: {$report['@generated']}</p>
    <p>Target: {$report['site'][0]['@name']}</p>

    <h2>Alerts</h2>
HTML;

foreach ($report['site'][0]['alerts'] as $alert) {
    $riskClass = strtolower(explode(' ', $alert['riskdesc'])[0]);
    $html .= <<<HTML
    <div class="alert {$riskClass}">
        <h3>{$alert['name']} - {$alert['riskdesc']}</h3>
        <p><strong>Description:</strong> {$alert['desc']}</p>
        <p><strong>Solution:</strong> {$alert['solution']}</p>
        <table>
            <tr>
                <th>URI</th>
                <th>Method</th>
                <th>Parameter</th>
            </tr>
HTML;

    foreach ($alert['instances'] as $instance) {
        $html .= <<<HTML
            <tr>
                <td>{$instance['uri']}</td>
                <td>{$instance['method']}</td>
                <td>{$instance['param']}</td>
            </tr>
HTML;
    }

    $html .= <<<HTML
        </table>
    </div>
HTML;
}

$html .= <<<HTML
</body>
</html>
HTML;

file_put_contents('local-zap-results/zap-results.html', $html);

echo "Informes generados en el directorio local-zap-results/\n";
echo "- zap-results.json\n";
echo "- zap-results.html\n"; 