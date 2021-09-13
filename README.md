# Certificates for CiviCRM

This extension allows sites administrator to configure certificates and attached them to a CiviCRM entity (Membership, Cases, Awards, Events).
The certificate becomes downloadable for users and site administrators when the configured condition for the certificate is satisfied.

Provides the following functionalities:
- An interface to configure Certificates and link them to a CiviCRM entity (Membership, Cases, Awards, Events).
- A certificate download link; when certificate configured conditions are satisfied.
- A space within the Self-service portal for users to download certificates that they are eligible for. The certificate should be available to send by email


# Dependencies
To be able to use this extension for Cases, you will need to install the following extension:

- [Civicase extension](https://github.com/compucorp/uk.co.compucorp.civicase)

# Installation (git/cli)
To install the extension on an existing CiviCRM site:

```bash
# Navigate to your extension directory, e.g.
cd sites/default/files/civicrm/ext

# Download and enable the extension dependencies
git clone --depth 1 https://github.com/civicrm/org.civicrm.shoreditch.git
git clone --depth 1 --no-single-branch https://github.com/compucorp/uk.co.compucorp.civicase.git
git clone --depth 1 https://github.com/compucorp/uk.co.compucorp.usermenu.git
cv en shoreditch usermenu civicase
# Download and enable the extension

git clone --depth 1 https://github.com/compucorp/uk.co.compucorp.certificate
cv en uk.co.compucorp.certificate

```