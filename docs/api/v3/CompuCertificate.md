# CompuCertificate.getrelationshipcertificates

Retrieve certificates that a contact can access via relationships.

## Parameters

| Name | Type | Required | Description |
| ---- | ---- | -------- | ----------- |
| `entity` | string | No | Entity type to fetch certificates for. Defaults to `case`. |
| `contact_id` | int | No | Contact requesting certificates. Defaults to the logged-in contact. |
| `primary_contact_id` | int | No | Restrict results to certificates whose primary (related) contact matches this ID. |

## Response
The response is an array of certificate objects, each with the following fields:
| Field | Type | Description |
| ----- | ---- | ----------- |
| `id` | int | The unique ID of the certificate. |
| `entity` | string | The entity type the certificate is associated with (e.g., `case`, `membership`). |
| `contact_id` | int | The ID of the contact requesting the certificate. |
| `primary_contact_id` | int | The ID of the primary (related) contact for the certificate. |
| `related_contact` | int | The ID of the contact related to the certificate (typically the primary contact or the contact for whom the certificate is issued). |
| ... | ... | Other standard certificate fields. |

## Examples

### Filter certificates to a specific primary contact

```php
$result = civicrm_api3('CompuCertificate', 'getrelationshipcertificates', [
  'entity' => 'membership',
  'contact_id' => 101,
  'primary_contact_id' => 202,
]);
```

The response contains only certificates where the related contact is `202`.
