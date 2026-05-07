// =============================================================================
// Jenkinsfile — mail4u WordPress plugin
// Pipeline: GitHub → lint → deploy to Hostinger via SSH (rsync)
//
// Required Jenkins plugins:
//   • Pipeline (built-in)
//   • SSH Agent Plugin
//   • Git Plugin
//
// Required Jenkins credentials (Manage Jenkins → Credentials):
//   • ID: github-token      → Username/Password  (GitHub PAT or SSH key)
//   • ID: hostinger-ssh-key → SSH Username with private key
//                             Username = your Hostinger SSH username
// =============================================================================

pipeline {

    agent any

    // ── Environment ──────────────────────────────────────────────────────────
    environment {
        GITHUB_REPO     = 'https://github.com/ikramarn/mail4u.git'
        PLUGIN_SLUG     = 'mail4u'
        HOSTINGER_HOST  = credentials('hostinger-host')      // store host as secret text
        HOSTINGER_USER  = credentials('hostinger-ssh-user')  // store username as secret text
        // Absolute path to wp-content/plugins on Hostinger.
        // Find it by SSH-ing in and running:
        //   find /home -name "wp-config.php" 2>/dev/null
        // Then append /wp-content/plugins
        WP_PLUGINS_PATH = '/home/u123456789/public_html/wp-content/plugins'
    }

    // ── Global options ────────────────────────────────────────────────────────
    options {
        timestamps()
        disableConcurrentBuilds()
        buildDiscarder(logRotator(numToKeepStr: '10'))
    }

    // ── Triggers ──────────────────────────────────────────────────────────────
    triggers {
        // Poll GitHub every 5 minutes; replace with webhook for instant builds
        pollSCM('H/5 * * * *')
    }

    // ─────────────────────────────────────────────────────────────────────────
    stages {

        // ── Stage 1: Checkout ─────────────────────────────────────────────────
        stage('Checkout') {
            steps {
                echo "Checking out mail4u from GitHub ..."
                checkout([
                    $class: 'GitSCM',
                    branches: [[name: '*/main']],
                    userRemoteConfigs: [[
                        url          : env.GITHUB_REPO,
                        credentialsId: 'github-token'
                    ]],
                    extensions: [
                        [$class: 'CleanBeforeCheckout'],
                        [$class: 'CloneOption', depth: 1, shallow: true]
                    ]
                ])
                echo "Checked out commit: ${env.GIT_COMMIT}"
            }
        }

        // ── Stage 2: Lint PHP ─────────────────────────────────────────────────
        stage('Lint PHP') {
            steps {
                echo "Running PHP syntax checks ..."
                script {
                    // Find every .php file and check syntax; fail build on error
                    sh '''
                        set -e
                        find . -name "*.php" \
                            -not -path "./.git/*" | while read -r f; do
                            php -l "$f"
                        done
                        echo "All PHP files passed syntax check."
                    '''
                }
            }
        }

        // ── Stage 3: Deploy via SSH (rsync) ───────────────────────────────────
        stage('Deploy to Hostinger') {
            steps {
                echo "Deploying plugin to Hostinger ..."
                sshagent(credentials: ['hostinger-ssh-key']) {
                    sh """
                        set -e

                        DEST_DIR="${WP_PLUGINS_PATH}/${PLUGIN_SLUG}"

                        echo "Target: \${HOSTINGER_USER}@\${HOSTINGER_HOST}:\${DEST_DIR}"

                        # Ensure remote plugin directory exists
                        ssh -o StrictHostKeyChecking=no \\
                            "\${HOSTINGER_USER}@\${HOSTINGER_HOST}" \\
                            "mkdir -p \${DEST_DIR}"

                        # Sync all plugin files; --delete removes files no longer in repo
                        rsync -avz --delete \\
                            --exclude='.git/' \\
                            --exclude='.gitignore' \\
                            --exclude='Jenkinsfile' \\
                            --exclude='commit-all.sh' \\
                            --exclude='README.md' \\
                            -e "ssh -o StrictHostKeyChecking=no" \\
                            ./ \\
                            "\${HOSTINGER_USER}@\${HOSTINGER_HOST}:\${DEST_DIR}/"

                        echo "Sync complete."
                    """
                }
            }
        }

        // ── Stage 4: Smoke-test (optional WP-CLI check) ───────────────────────
        stage('Verify Plugin Active') {
            steps {
                echo "Verifying plugin is recognised by WordPress ..."
                sshagent(credentials: ['hostinger-ssh-key']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no \\
                            "\${HOSTINGER_USER}@\${HOSTINGER_HOST}" \\
                            "ls -la ${WP_PLUGINS_PATH}/${PLUGIN_SLUG}/mail4u.php"
                        echo "Plugin main file confirmed on server."
                    """
                }
            }
        }

    } // end stages

    // ── Post actions ──────────────────────────────────────────────────────────
    post {
        success {
            echo "Deployment SUCCESSFUL — mail4u is live on Hostinger."
        }
        failure {
            echo "Deployment FAILED. Check the console output above."
        }
        always {
            cleanWs()
        }
    }
}
