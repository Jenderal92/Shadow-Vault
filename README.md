# Shadow Vault 🔐

<img width="1200" height="1200" alt="Shadow Vault" src="https://github.com/user-attachments/assets/0ea239da-0632-48d7-947a-ab9064704b7e" />

**Shadow Vault** is a lightweight PHP web tool designed for **system administrators** to securely add new user credentials to `shadow` files on shared hosting environments. It automatically detects existing shadow aging format, uses SHA-512 password hashing, supports multiple write fallback methods, and even provides **one-click auto-login** to cPanel webmail (port 2096).

> ⚠️ **Disclaimer**: This tool is intended for **legitimate server management only**. Unauthorized access or use on systems you do not own is illegal. Use at your own risk.

## Features

- ✅ **SHA-512 password hashing** with random salt (`$6$`)
- ✅ **Dynamic shadow aging detection** – reads existing entries and preserves the original `lastchange:min:max:warn:inactive:expire:reserved` format
- ✅ **Multi-method write** – 14 fallback methods including `file_put_contents`, `fopen`/`flock`, temp+rename, `system`, `exec`, `shell_exec`, `proc_open`, `popen`, `passthru`, `copy`, `stream_copy_to_stream`, and more
- ✅ **Auto‑discovers all shadow files** inside `/home/*/etc/*/shadow` – no manual configuration
- ✅ **Auto‑detects domain** from existing shadow paths or server hostname (dropdown selection)
- ✅ **Virtual passwd support** (optional) – writes to `/etc/virtual/*/passwd` for mail servers that require it; non‑critical if login already works
- ✅ **One‑click auto‑login** – automatically submits credentials to `https://domain:2096/login/` (cPanel webmail standard) using POST with `user`/`pass` fields
- ✅ **Clickable login URL** – opens webmail login page in a new tab
- ✅ **Copy password button** – modern clipboard API with fallback to `execCommand`
- ✅ **Responsive table output** – works on desktop and mobile
- ✅ **No database required** – single PHP file

## How It Works

1. The script locates the current user via `get_current_user()`.
2. It scans `/home/username/etc/*/shadow` for all existing shadow files (one per domain). If none exist, it creates a new shadow file under `/home/username/etc/{domain}/shadow`.
3. Domains are auto‑detected and displayed in a dropdown selector.
4. You provide a **local part** (e.g., `john`) and a **password**.
5. The script:
   - Generates a SHA‑512 hash of the password.
   - Extracts the **aging suffix** from any valid line in the existing shadow file (fallback: `19400:0:99999:7:::`).
   - Appends a new line: `localpart:hash:aging_suffix` to **every** shadow file found (or the newly created one).
   - (Optional) Attempts to write the same credentials to `/etc/virtual/{domain}/passwd` – failure does not affect login if the system uses shadow authentication.
6. On success, it displays a table with:
   - Clickable webmail URL (`https://domain:2096`)
   - Full username (`localpart@domain`)
   - Password (with copy button)
   - **Auto Login button** – opens a new tab and automatically logs in using POST request.

## Installation

1. Upload the `shadow.php` file to any directory inside your hosting account (e.g., `public_html/shadow/`).
2. Ensure the script is **protected** from public access (see Security Recommendations below).
3. Open the URL in a browser – no database or configuration required.

## Usage

1. Access the tool via your browser.
2. Select the domain from the auto‑detected dropdown (or it will be auto‑selected if only one domain exists).
3. Enter a **local part** (username without domain) and a **password**.
4. Click **“Add Account”**.
5. If successful, you will see a table with all domains and their credentials.
6. Use the **Auto Login** button to instantly log into webmail, or click the URL to open the login page manually.

## Security Recommendations

- **Restrict access** using `.htaccess` with Basic Authentication or IP whitelisting.
- Place the script in a **non‑guessable directory** (e.g., `/randomhash/`).
- After use, **remove the script** from the server.
- Do not share the URL with anyone.

## Requirements

- PHP 5.4+ (compatible with older versions using fallback `random_bytes` polyfill)
- Write permission to the target `shadow` directory (`/home/username/etc/`)
- For auto‑login: JavaScript must be enabled in the browser

## Example

**Input:**  
- Domain: `example.com` (auto‑detected)  
- Local part: `support`  
- Password: `MyStr0ng!`

**Output table:**

| Login URL | Username | Password | Action |
|-----------|----------|----------|--------|
| https://example.com:2096 SSL | support@example.com | MyStr0ng! | copy / auto-login |

**Entry added to shadow file:**
```

support:$6$randomSalt$hashedPassword:19400:0:99999:7:::

```

## File Structure

```

shadow-vault/
└── shadow.php          # Main script (upload to your server)

```

## Error Handling

- If no shadow files are found and creation fails → `No shadow files found. Check directory structure.`
- If write to virtual passwd fails → warning message (login usually still works)
- If all 14 write methods fail → `Failed to write to shadow (all methods failed).`
- If input is empty → `Please fill username, password, and ensure domain is available.`

## Auto‑Login Technical Details

The script generates a temporary HTML form that submits a POST request to:
- **URL**: `https://domain:2096/login/`
- **Fields**: `user` (full email address) and `pass` (plain password)

This matches the standard cPanel webmail login form. If your webmail uses different endpoints or field names, you can modify the JavaScript `autoLogin()` function accordingly.

## License

MIT License – free to use, modify, and distribute. The author is not responsible for any misuse.

## Contributing

Feel free to open issues or pull requests for improvements. Keep the tool simple and educational.

## ⚠️ DISCLAIMER – READ CAREFULLY

> **This tool is intended for authorized server administrators only.**  
> By using this software, you confirm that you have **explicit permission** to modify shadow files on the server where it is executed.
>
> - Unauthorized access or use on systems you do not own is **illegal**.
> - The author assumes **no liability** for any damage, data loss, or legal consequences resulting from misuse.
> - **Use at your own risk.** Always test in a safe environment first.
> - If you are not the server owner or an authorized admin, **stop now** and delete this script immediately.
> ---
> More Disclaimer You can see the disclaimer on the cover of Jenderal92. You can check it [HERE !!!](https://github.com/Jenderal92/)
```
