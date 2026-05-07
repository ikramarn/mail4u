# Jenkins Setup Guide — mail4u → Hostinger

## 1. Find your WordPress plugins path on Hostinger

SSH into Hostinger and run:
```bash
find /home -name "wp-config.php" 2>/dev/null
```
The plugins folder is at the same level: `…/wp-content/plugins`
Update `WP_PLUGINS_PATH` in the Jenkinsfile accordingly.

---

## 2. Required Jenkins credentials

Go to **Manage Jenkins → Credentials → (global) → Add Credential** and create four entries:

| Credential ID | Kind | Value |
|---|---|---|
| `github-token` | Username with password | GitHub username + Personal Access Token |
| `hostinger-ssh-key` | SSH Username with private key | Paste your **private** SSH key; username = Hostinger SSH user |
| `hostinger-host` | Secret text | Your Hostinger SSH hostname (e.g. `srv123.hostinger.com`) |
| `hostinger-ssh-user` | Secret text | Your Hostinger SSH username (e.g. `u123456789`) |

### Generating an SSH key pair (if you don't have one)
```bash
ssh-keygen -t ed25519 -C "jenkins-deploy" -f ~/.ssh/jenkins_hostinger
```
- **Private key** → paste into `hostinger-ssh-key` Jenkins credential
- **Public key** → paste into Hostinger hPanel → **SSH Keys** section

---

## 3. Required Jenkins plugins

Install via **Manage Jenkins → Plugins → Available**:
- **SSH Agent Plugin**
- **Git Plugin** (usually pre-installed)

---

## 4. Create the pipeline job

1. **New Item** → name it `mail4u-deploy` → choose **Pipeline**
2. Under **Pipeline**:
   - Definition: `Pipeline script from SCM`
   - SCM: `Git`
   - Repository URL: `https://github.com/ikramarn/mail4u.git`
   - Credentials: `github-token`
   - Branch: `*/main`
   - Script Path: `Jenkinsfile`
3. Save → **Build Now**

---

## 5. Commit existing code first (one-time, run locally)

On your machine (Git Bash / WSL / Linux):
```bash
cd /path/to/mail4u
bash commit-all.sh
```
This stages and commits every file individually, then pushes to GitHub.
Jenkins will detect the push (or poll within 5 min) and trigger the deployment.

---

## 6. Webhook (optional — instant builds instead of polling)

In GitHub → repo **Settings → Webhooks → Add webhook**:
- Payload URL: `http://<your-jenkins-url>/github-webhook/`
- Content type: `application/json`
- Event: **Just the push event**

Then in the Jenkinsfile, replace:
```groovy
pollSCM('H/5 * * * *')
```
with:
```groovy
githubPush()
```

---

## Pipeline stages

| # | Stage | What it does |
|---|---|---|
| 1 | Checkout | Shallow-clones `main` from GitHub |
| 2 | Lint PHP | `php -l` on every `.php` file — fails build on syntax error |
| 3 | Deploy to Hostinger | `rsync` over SSH → syncs plugin files to WP plugins folder |
| 4 | Verify Plugin Active | Confirms `mail4u.php` exists on the remote server |
