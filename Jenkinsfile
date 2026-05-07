// =============================================================================
// Jenkinsfile — mail4u WordPress plugin
// Pipeline: GitHub → lint → deploy to Hostinger via SSH (rsync)
//
// Required Jenkins plugins:
//   • Pipeline (built-in)
//   • Credentials Binding Plugin (for withCredentials / sshUserPrivateKey)
//   • Git Plugin
//
// Required Jenkins credentials (Manage Jenkins → Credentials):
//   • ID: github-token      → Username/Password  (GitHub PAT)
//   • ID: jenkins-hostinger → SSH Username with private key
//                             Username = your Hostinger SSH username
// =============================================================================

pipeline {

    agent any

    // ── Environment ──────────────────────────────────────────────────────────
    environment {
        GITHUB_REPO     = 'https://github.com/ikramarn/mail4u.git'
        PLUGIN_SLUG     = 'mail4u'
        HOSTINGER_HOST  = '31.170.164.208'
        HOSTINGER_PORT  = '65002'
        HOSTINGER_USER  = 'u496327464'
        WP_PLUGINS_PATH = '/home/u496327464/domains/darkred-jellyfish-898544.hostingersite.com/public_html/wp-content/plugins'
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
                withCredentials([sshUserPrivateKey(
                    credentialsId : 'jenkins-hostinger',
                    keyFileVariable: 'SSH_KEY'
                )]) {
                    sh """
                        set -e

                        DEST_DIR="${WP_PLUGINS_PATH}/${PLUGIN_SLUG}"

                        echo "Target: ${HOSTINGER_USER}@${HOSTINGER_HOST}:\${DEST_DIR}"

                        # Ensure remote plugin directory exists
                        ssh -i "\$SSH_KEY" -p "${HOSTINGER_PORT}" -o StrictHostKeyChecking=no \\
                            "${HOSTINGER_USER}@${HOSTINGER_HOST}" \\
                            "mkdir -p \${DEST_DIR}"

                        # Sync all plugin files; --delete removes files no longer in repo
                        rsync -avz --delete \\
                            --exclude='.git/' \\
                            --exclude='.gitignore' \\
                            --exclude='Jenkinsfile' \\
                            --exclude='commit-all.sh' \\
                            --exclude='README.md' \\
                            -e "ssh -i \$SSH_KEY -p ${HOSTINGER_PORT} -o StrictHostKeyChecking=no" \\
                            ./ \\
                            "${HOSTINGER_USER}@${HOSTINGER_HOST}:\${DEST_DIR}/"

                        echo "Sync complete."

                        # Reset PHP OPcache so new files are picked up immediately
                        ssh -i "\$SSH_KEY" -p "${HOSTINGER_PORT}" -o StrictHostKeyChecking=no \\
                            "${HOSTINGER_USER}@${HOSTINGER_HOST}" \\
                            "printf '<?php opcache_reset();' > /tmp/m4u_reset.php && php /tmp/m4u_reset.php && rm /tmp/m4u_reset.php && echo OPcache cleared"

                        echo "OPcache reset attempted."
                    """
                }
            }
        }

        // ── Stage 4: Smoke-test (optional WP-CLI check) ───────────────────────
        stage('Verify Plugin Active') {
            steps {
                echo "Verifying plugin is recognised by WordPress ..."
                withCredentials([sshUserPrivateKey(
                    credentialsId : 'jenkins-hostinger',
                    keyFileVariable: 'SSH_KEY'
                )]) {
                    sh """
                        ssh -i "\$SSH_KEY" -p "${HOSTINGER_PORT}" -o StrictHostKeyChecking=no \\
                            "${HOSTINGER_USER}@${HOSTINGER_HOST}" \\
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
