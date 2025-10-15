# CompuCertificate.getrelationshipcertificates

Retrieve certificates that a contact can access via relationships.

## Parameters

| Name | Type | Required | Description |
| ---- | ---- | -------- | ----------- |
| `entity` | string | No | Entity type to fetch certificates for. Defaults to `case`. |
| `contact_id` | int | No | Contact requesting certificates. Defaults to the logged-in contact. |
| `primary_contact_id` | int | No | Restrict results to certificates whose primary (related) contact matches this ID. |

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
