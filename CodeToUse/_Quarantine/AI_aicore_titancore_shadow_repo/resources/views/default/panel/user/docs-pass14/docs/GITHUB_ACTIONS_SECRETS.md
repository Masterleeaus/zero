# GitHub Actions Secrets Checklist

Add these secrets to your repository (Settings → Secrets → Actions) depending on which features you use.

## Required / Highly recommended
- `GITHUB_TOKEN` (automatically provided by GitHub)
- `DOCKERHUB_USERNAME` — Docker Hub username (if pushing to Docker Hub)
- `DOCKERHUB_TOKEN` — Docker Hub access token or password
- `REGISTRY_USERNAME` — Generic container registry user (if using another registry)
- `REGISTRY_TOKEN` — Generic container registry token

## Kubernetes / CD
- `KUBE_CONFIG` — Base64-encoded kubeconfig for the cluster (used by kubectl/helm)
- `K8S_NAMESPACE` — Namespace to deploy into (e.g., `aiplatform`)

## Helm / Chart repos
- `HELM_REPO_USERNAME` — Username for private Helm repo (optional)
- `HELM_REPO_PASSWORD` — Password / token for private Helm repo (optional)

## Cloud providers (if using ECR/GCR/AKS/etc.)
- AWS: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`
- GCP: `GCP_SA_KEY` (base64-encoded service account JSON)
- Azure: `AZURE_CREDENTIALS` (service principal JSON)

## Optional
- `SLACK_WEBHOOK_URL` — Post CI/CD notifications to Slack
- `SENTRY_DSN` — For release tracking uploads
- `RELEASE_SIGNING_KEY` — Private key for signing artifacts (use a secure secrete manager)

## How to create KUBE_CONFIG secret (example)
1. On a machine with `kubectl` configured for your cluster:
   ```bash
   cat ~/.kube/config | base64 | tr -d '\n' | pbcopy
   ```
2. In GitHub repository → Settings → Secrets → Actions → New repository secret
   - Name: `KUBE_CONFIG`
   - Value: (paste base64 string)

Keep secrets minimal in scope (use service accounts with least privilege).
