# Certificates for CiviCRM

## Table of contents
* [Overview](#overview)
* [How it Works](#how-it-works)
  + [Certificate Configuration Page](#certificate-configuration-page)
  + [Configure a New Certificate](#configure-a-new-certificate)
  + [Download a Certificate](#download-a-certificate)
  + [Certificate Template](#certificate-template)
* [Installation](#installation)
  + [Dependencies](#dependencies)
  + [Installation via git/cli](#installation-via-gitcli)
## Overview
There are different scenarios for which an organization would like to generate proof that an event occurred or a contact in their system has attained a certain status. This could be a contact attending an event, a contact becoming a member of a membership type or a client case has been resolved. In any of these instances, a proof of occurrence would need to be available for the contact and organization administrator in a downloadable format (PDF), this proof of occurrence is referred to as a certificate.

This extension allows users to configure these certificates and attach them to CiviCRM entities, as well as specify conditions under which they become available. The certificate becomes downloadable for users when the specified condition for the certificate is satisfied.

Provides the following functionalities:
- An interface to configure Certificates and link them to a CiviCRM entity (Membership, Cases, Awards, Events).
- A certificate download link; when conditions for a configured certificate are satisfied.

## How it Works
### Certificate Configuration Page
The Certificate extension adds an additional option under `Administer>Certificate` that displays a list of all certificate configurations in the site.

<img width="1414" alt="Screenshot 2022-10-24 at 14 32 53" src="https://user-images.githubusercontent.com/85277674/197538128-f3928d4c-f79d-4a9e-9c82-7ea5f8e7b598.png">

### Configure a New Certificate
On the certificate configuration page clicking on the `New Certificate` button displays a modal with the form to configure a new certificate, similar to the image below
<img width="1438" alt="Screenshot 2022-10-24 at 14 40 58" src="https://user-images.githubusercontent.com/85277674/197539746-04017f3c-b15b-44e7-bbc0-75ef5e8080a5.png">

### Download a Certificate
#### Case Certificate
A download link would be shown in the case menu if there's an appropriate certificate configured for case type, case status, and the current user has sufficient permissions to download a certificate.
![image](https://user-images.githubusercontent.com/85277674/197549313-47aef60f-0255-40d3-a7a5-3b77119d38f2.png)

#### Event Certificate
A download button would be shown at the bottom of the Participant record view if there's an appropriate certificate configured for the event type, participant status, participant role and the user has sufficient permission to download a certificate.
![image](https://user-images.githubusercontent.com/85277674/197550173-516b3e63-ebca-42cc-b4a3-39bdb7a929d2.png)

#### Membership Certificate
A download button would be shown at the bottom of the Membership record view if there's an appropriate certificate configured for the membership type, membership status and the user has sufficient permission to download a certificate.
![image](https://user-images.githubusercontent.com/85277674/197551256-b72fb9dc-379c-4e7d-afb2-f64c16710f7f.png)

### Certificate Template
The standard CiviCRM message templates are used as the certificate template, which implies they can be designed and formatted as the user desires with support for custom entity tokens. Almost all fields for each entity are supported as a token, e.g. `{certificate_event.title}`, users can use the token dropdown in the message template editor to see available tokens.

![Certificate template](https://user-images.githubusercontent.com/85277674/197568662-233ad63a-fcef-4ddf-bfe5-8c2cfbc6aa2f.gif)


## Installation
### Dependencies

- PHP 7.4+
- CiviCRM 5.39.1+
- To be able to use this extension for Case entity, you will need to install the [Civicase extension](https://github.com/compucorp/uk.co.compucorp.civicase)
## Installation via git/cli
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
