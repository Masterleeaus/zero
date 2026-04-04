# Git Systems Integrations Documentation

## Overview

AIPlatform supports multiple Git systems for version control and CI/CD.

## Supported Systems

- **GitHub**: Primary hosting.
- **GitLab**: Alternative CI/CD.
- **GitFlic, GitVerse, SourceCraft**: Domestic systems.
- **International**: Canadian, Israeli, Arabic, Australian, Chinese systems.

## Setup

1. Choose a Git system and host the repo.
2. Configure CI/CD pipelines (e.g., .github/workflows/, .gitlab-ci.yml).
3. Set up webhooks for automation.

## Usage

- Push code to trigger builds.
- Use issues and PRs for collaboration.

## Migration

- Scripts to migrate between systems.

## Troubleshooting

- Check webhook delivery.
- Verify access tokens.



---
## Titan Zero Federated Node Adjustment

This project now operates under a **device‑first federated node runtime model**:

• Devices act as primary execution nodes  
• Server coordinates validation, promotion, audit, and relay only  
• State changes must originate as signals before canonical promotion  
• Automation executes through Pulse handlers only  
• Corrections occur via Rewind — never destructive mutation  
• Tenant boundary enforced by `company_id`  
• Sync exchanges intent envelopes, not database state

Execution spine:

Device → Process → Signal → Validation → Canonical Event → Pulse → Rewind → Domain Update

