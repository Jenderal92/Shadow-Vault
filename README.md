# Shadow Vault 🔐

**Shadow Vault** is a lightweight PHP web tool designed for **system administrators** to securely add new user credentials to `shadow` files on shared hosting . It automatically detects existing shadow aging format, uses SHA-512 password hashing, and supports multiple write fallback methods.

> ⚠️ **Disclaimer**: This tool is intended for **legitimate server management only**. Unauthorized access or use on systems you do not own is illegal. Use at your own risk.

## Features

- ✅ **SHA-512 password hashing** with random salt (`$6$`)
- ✅ **Dynamic shadow aging detection** – reads existing entries and preserves the original `lastchange:min:max:warn:inactive:expire:reserved` format
- ✅ **Multi-method write** – falls back to `fopen`/`fwrite` or even `exec` (shell echo) if `file_put_contents` fails
- ✅ **Auto‑discovers all shadow files** inside `/home/*/etc/*/shadow`
- ✅ **Webmail login helper** – displays domain list with pre‑formatted access URLs (port 2096)
- ✅ **User‑friendly table output** – responsive design, copy password button
- ✅ **Mobile & desktop responsive** – fits any screen size

## How It Works

1. The script locates the current user via `get_current_user()`.
2. It scans `/home/username/etc/*/shadow` for all existing shadow files (one per domain).
3. You provide a **local part** (e.g., `john`) and a **password**.
4. The script:
   - Generates a SHA‑512 hash of the password.
   - Extracts the **aging suffix** from any valid line in the existing shadow file (falls back to `19400:0:99999:7:::` if none found).
   - Appends a new line: `localpart:hash:aging_suffix` to **every** shadow file found.
5. On success, it displays a table with each domain, the full username (`localpart@domain`), and the password (with a copy button).

## Installation

1. Upload the `shadow.php` file to any directory inside your hosting account (e.g., `public_html/shadow/`).
2. Ensure the script is **protected** from public access (see Security Recommendations below).
3. Open the URL in a browser – no database or configuration required.

## Usage

1. Access the tool via your browser.
2. Enter a **local part** (username without domain) and a **password**.
3. Click **“Add to Shadow”**.
4. If successful, you will see a table listing all domains with the corresponding login URL (`https://domain:2096`), username (`localpart@domain`), and password.

## Security Recommendations

- **Restrict access** using `.htaccess` with Basic Authentication or IP whitelisting.
- Place the script in a **non‑guessable directory** (e.g., `/randomhash/`).
- After use, **remove the script** from the server.
- Do not share the URL with anyone.

## Requirements

- PHP 5.4+ (compatible with older versions using fallback `random_bytes` polyfill)
- Write permission to the target `shadow`  
- `exec()` function is **optional** – used only as last resort; if disabled, the first two methods still work.

## Example

**Input:**  
- Local part: `support`  
- Password: `MyStr0ng!`

**Output table:**

| Login (domain:2096) | Username | Password |
|---------------------|----------|----------|
| example.com:2096 | support@example.com | MyStr0ng! |
| myblog.net:2096 | support@myblog.net | MyStr0ng! |

The following line is added to each shadow file:
```
support:$6$randomSalt$hashedPassword:19400:0:99999:7:::
```

## File Structure

```
shadow-vault/
└── shadow.php          # Main script (copy to your server)
```

## Error Handling

- If no shadow files are found → `No shadow files found.`
- If all write methods fail → `Failed to write to shadow (all methods failed).`
- If input is empty → `Please fill in both username and password.`

## License

MIT License – free to use, modify, and distribute. The author is not responsible for any misuse.

## Contributing

Feel free to open issues or pull requests for improvements. Keep the tool simple and educational.

## ⚠️ DISCLAIMER – READ CAREFULLY
>
> **This tool is intended for authorized server administrators only.**  
> By using this software, you confirm that you have **explicit permission** to modify shadow files on the server where it is executed.
>
> - Unauthorized access or use on systems you do not own is **illegal**.
> - The author assumes **no liability** for any damage, data loss, or legal consequences resulting from misuse.
> - **Use at your own risk.** Always test in a safe environment first.
> - If you are not the server owner or an authorized admin, **stop now** and delete this script immediately.
> ---
> More Disclaimer You Can see the disclaimer on the cover of Jenderal92. You can check it [HERE !!!](https://github.com/Jenderal92/)

