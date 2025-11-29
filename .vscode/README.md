# FTP Upload Configuration

This workspace is configured to upload files to an FTP server using the SFTP extension.

**Current Configuration:** Manual upload mode with confirmation prompts (no auto-upload)

## Setup Instructions

1. **Install the SFTP Extension:**
   - Open VS Code Extensions (Ctrl+Shift+X)
   - Search for "SFTP" by Natizyskunk
   - Install the extension

2. **Configure FTP Credentials:**
   - Open `.vscode/sftp.json`
   - Update the following fields with your FTP server details:
     - `host`: Your FTP server address
     - `port`: FTP port (usually 21 for FTP, 22 for SFTP)
     - `username`: Your FTP username
     - `password`: Your FTP password
     - `remotePath`: Remote directory path on the server
     - `protocol`: Use "ftp" for FTP or "sftp" for SFTP

3. **Usage - Manual Upload (Recommended):**
   
   **Method 1: Right-Click Menu**
   - **Upload Single File:** Right-click on a file → "SFTP: Upload File" (will ask for confirmation)
   - **Upload Folder:** Right-click on a folder → "SFTP: Upload Folder" (will ask for confirmation)
   - **Upload All:** Right-click in Explorer → "SFTP: Upload All" (will ask for confirmation)
   
   **Method 2: Command Palette (Batch Upload)**
   - Press `Ctrl+Shift+P` (or `F1`)
   - Type "SFTP" to see all available commands:
     - `SFTP: Upload Active File` - Upload the currently open file
     - `SFTP: Upload Folder` - Upload a selected folder
     - `SFTP: Upload All` - Upload all files in workspace
     - `SFTP: Upload Changed Files` - Upload only modified files (like git commit)
     - `SFTP: Sync Local -> Remote` - Sync local changes to remote
   
   **Method 3: Keyboard Shortcuts**
   - You can set custom keyboard shortcuts in VS Code:
     - Go to File → Preferences → Keyboard Shortcuts
     - Search for "SFTP" and assign your preferred keys
   
   **Download Files:**
   - Right-click → "SFTP: Download File/Folder"
   - Or use Command Palette: `SFTP: Download File`

## Security Note

⚠️ **Important:** The `sftp.json` file contains sensitive credentials. Make sure to:
- Add `.vscode/sftp.json` to your `.gitignore` file to prevent committing credentials
- Use environment variables or VS Code's secret storage for production environments

## Batch Upload Workflow (Like GitHub)

To upload multiple files at once:

1. **Upload Changed Files Only:**
   - Use Command Palette (`Ctrl+Shift+P`)
   - Type: `SFTP: Upload Changed Files`
   - This uploads only files that have been modified (similar to git commit)

2. **Upload Selected Files:**
   - Select multiple files in Explorer (Ctrl+Click)
   - Right-click → "SFTP: Upload File" (uploads all selected files)

3. **Sync All Changes:**
   - Command Palette → `SFTP: Sync Local -> Remote`
   - This compares local and remote files and uploads differences

## Keyboard Shortcuts

You can customize keyboard shortcuts:
1. Go to File → Preferences → Keyboard Shortcuts (`Ctrl+K Ctrl+S`)
2. Search for "SFTP"
3. Assign shortcuts to:
   - `sftp.upload.activeFile` - Upload current file
   - `sftp.upload.folder` - Upload folder
   - `sftp.upload.all` - Upload all files
   - `sftp.upload.changedFiles` - Upload changed files only

## Alternative: Using Environment Variables

For better security, you can use environment variables:
- Remove `password` from `sftp.json`
- The extension will prompt you for the password when needed
- Or use VS Code's built-in secret storage

