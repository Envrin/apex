<?php
declare(strict_types = 1);

namespace apex\app\pkg;

use apex\app;
use apex\services\db;
use apex\services\debug;
use apex\services\redis;
use apex\services\utils\components;
use apex\app\exceptions\ComponentException;
use apex\app\pkg\package_config;
use apex\services\utils\date;
use apex\services\utils\io;


/**
 * Handles creating, adding, deleting, and updating components within 
 * packages.  Used for all package / upgrade functions. 
 */
class pkg_component
{




    // Set code templates
    private static $code_templates = array(
    'ajax' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxhamF4OwoKdXNlIGFwZXhcYXBwOwp1c2UgYXBleFxzZXJ2aWNlc1xkYjsKdXNlIGFwZXhcc2VydmljZXNcZGVidWc7CnVzZSBhcGV4XGFwcFx3ZWJcYWpheDsKCi8qKgogKiBIYW5kbGVzIHRoZSBBSkFYIGZ1bmN0aW9uIG9mIHRoaXMgY2xhc3MsIGFsbG93aW5nIAogKiBET00gZWxlbWVudHMgd2l0aGluIHRoZSBicm93c2VyIHRvIGJlIG1vZGlmaWVkIGluIHJlYWwtdGltZSAKICogd2l0aG91dCBoYXZpbmcgdG8gcmVmcmVzaCB0aGUgYnJvd3Nlci4KICovCmNsYXNzIH5hbGlhc34gZXh0ZW5kcyBhamF4CnsKCi8qKgogICAgKiBQcm9jZXNzZXMgdGhlIEFKQVggZnVuY3Rpb24uCiAqCiAqIFByb2Nlc3NlcyB0aGUgQUpBWCBmdW5jdGlvbiwgYW5kIHVzZXMgdGhlIAogKiBtb2V0aGRzIHdpdGhpbiB0aGUgJ2FwZXhcYWpheCcgY2xhc3MgdG8gbW9kaWZ5IHRoZSAKICogRE9NIGVsZW1lbnRzIHdpdGhpbiB0aGUgd2ViIGJyb3dzZXIuICBTZWUgCiAqIGRvY3VtZW50YXRpb24gZm9yIGR1cnRoZXIgZGV0YWlscy4KICovCnB1YmxpYyBmdW5jdGlvbiBwcm9jZXNzKCkKewoKICAgIC8vIFBlcmZvcm0gbmVjZXNzYXJ5IGFjdGlvbnMKCn0KCn0KCg==',
    'autosuggest' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxhdXRvc3VnZ2VzdDsKCnVzZSBhcGV4XGFwcDsKdXNlIGFwZXhcc2VydmljZXNcZGI7CnVzZSBhcGV4XHNlcnZpY2VzXGRlYnVnOwoKCi8qKgogKiBUaGUgYXV0by1zdWdnZXN0IGNsYXNzIHRoYXQgYWxsb3dzIGRpc3BsYXlpbmcgb2YgCiAqIGRyb3AgZG93biBsaXN0cyB0aGF0IGFyZSBhdXRvbWF0aWNhbGx5IGZpbGxlZCB3aXRoIHN1Z2dlc3Rpb25zLgogKi8KY2xhc3MgfmFsaWFzfgp7CgovKioKICAgICogU2VhcmNoIGFuZCBkZXRlcm1pbmUgc3VnZ2VzdGlvbnMuCiAqCiAqIFNlYXJjaGVzIGRhdGFiYXNlIHVzaW5nIHRoZSBnaXZlbiAkdGVybSwgYW5kIHJldHVybnMgYXJyYXkgb2YgcmVzdWx0cywgd2hpY2ggCiAqIGFyZSB0aGVuIGRpc3BsYXllZCB3aXRoaW4gdGhlIGF1dG8tc3VnZ2VzdCAvIGNvbXBsZXRlIGJveC4KICoKICogICAgIEBwYXJhbSBzdHJpbmcgJHRlcm0gVGhlIHNlYXJjaCB0ZXJtIGVudGVyZWQgaW50byB0aGUgdGV4dGJveC4KICogICAgIEByZXR1cm4gYXJyYXkgQW4gYXJyYXkgb2Yga2V5LXZhbHVlIHBhcmlzLCBrZXlzIGFyZSB0aGUgdW5pcXVlIElEIyBvZiB0aGUgcmVjb3JkLCBhbmQgdmFsdWVzIGFyZSBkaXNwbGF5ZWQgaW4gdGhlIGF1dG8tc3VnZ2VzdCBsaXN0LgogKi8KcHVibGljIGZ1bmN0aW9uIHNlYXJjaChzdHJpbmcgJHRlcm0pOmFycmF5IAp7CgogICAgLy8gR2V0IG9wdGlvbnMKICAgICRvcHRpb25zID0gYXJyYXkoKTsKCgogICAgLy8gUmV0dXJuCiAgICByZXR1cm4gJG9wdGlvbnM7Cgp9Cgp9Cgo=',
    'cli' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxjbGk7Cgp1c2UgYXBleFxhcHA7CnVzZSBhcGV4XHNlcnZpY2VzXGRiOwp1c2UgYXBleFxzZXJ2aWNlc1xkZWJ1ZzsKCi8qKgogKiBDbGFzcyB0byBoYW5kbGUgdGhlIGN1c3RvbSBDTEkgY29tbWFuZCAKICogYXMgbmVjZXNzYXJ5LgogKi8KY2xhc3MgfmFsaWFzfgp7CgovKioKICogRXhlY3V0ZXMgdGhlIENMSSBjb21tYW5kLgogKiAgCiAqICAgICBAcGFyYW0gaXRlcmFibGUgJGFyZ3MgVGhlIGFyZ3VtZW50cyBwYXNzZWQgdG8gdGhlIGNvbW1hbmQgY2xpbmUuCiAqLwpwdWJsaWMgZnVuY3Rpb24gcHJvY2VzcyguLi4kYXJncykKewoKCgp9Cgp9Cgo=',
    'controller' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxjb250cm9sbGVyXH5wYXJlbnR+OwoKdXNlIGFwZXhcYXBwOwp1c2UgYXBleFxzZXJ2aWNlc1xkYjsKdXNlIGFwZXhcc2VydmljZXNcZGVidWc7CnVzZSBhcGV4XH5wYWNrYWdlflxjb250cm9sbGVyXH5wYXJlbnR+OwoKCi8qKgogKiBDaGlsZCBjb250cm9sbGVyIGNsYXNzLCB3aGljaCBwZXJmb3JtcyB0aGUgbmVjZXNzYXJ5IAogKiBhY3Rpb25zIC8gbWV0aG9kcyBkZXBlbmRpbmcgb24gdGhlIHN0cnVjdHVyZSBvZiB0aGUgCiAqIHBhcmVudCBjb250cm9sbGVyLgogKi8KY2xhc3MgfmFsaWFzfiBpbXBsZW1lbnRzIH5wYXJlbnR+CnsKCi8qKgogKiBCbGFuayBQSFAgY2xhc3MgZm9yIHRoZSBjb250cm9sbGVyLiAgRm9yIHRoZSAKICogY29ycmVjdCBtZXRob2RzIGFuZCBwcm9wZXJ0aWVzIGZvciB0aGlzIGNsYXNzLCBwbGVhc2UgCiAqIHJldmlldyB0aGUgYWJzdHJhY3QgY2xhc3MgbG9jYXRlZCBhdDoKICogICAgIC9zcmMvfnBhY2thZ2V+L2NvbnRyb2xsZXIvfnBhY2thZ2V+LnBocAogKgogKi8KCgp9Cgo=',
    'controller_parent' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxjb250cm9sbGVyOwoKLyoqCiAqIFRoZSBwYXJlbnQgaW50ZXJmYWNlIGZvciB0aGUgY29udHJvbGxlciwgYWxsb3dpbmcgeW91IHRvIAogKiBkZWZpbmUgd2hpY2ggbWV0aG9kcyBhcmUgcmVxdWlyZWQgd2l0aGluIGFsbCBjaGlsZCBjb250cm9sbGVycy4KICovCmludGVyZmFjZSB+YWxpYXN+CnsKCgoKfQoK',
    'cron' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxjcm9uOwoKdXNlIGFwZXhcYXBwOwp1c2UgYXBleFxzZXJ2aWNlc1xkYjsKdXNlIGFwZXhcc2VydmljZXNcZGVidWc7CgovKioKICogQ2xhc3MgdGhhdCBhbmRsZXMgdGhlIGNyb250YWIgam9iLgogKi8KY2xhc3MgfmFsaWFzfgp7CgogICAgLy8gUHJvcGVydGllcwogICAgcHVibGljICR0aW1lX2ludGVydmFsID0gJ0kzMCc7CiAgICBwdWJsaWMgJG5hbWUgPSAnfmFsaWFzfic7CgovKioKICogUHJvY2Vzc2VzIHRoZSBjcm9udGFiIGpvYi4KICovCnB1YmxpYyBmdW5jdGlvbiBwcm9jZXNzKCkKewoKCgp9Cgp9Cg==',
    'form' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxmb3JtOwoKdXNlIGFwZXhcYXBwOwp1c2UgYXBleFxzZXJ2aWNlc1xkYjsKdXNlIGFwZXhcc2VydmljZXNcZGVidWc7CgoKLyoqIAogKiBDbGFzcyBmb3IgdGhlIEhUTUwgZm9ybSB0byBlYXNpbHkgZGVzaWduIGZ1bGx5IGZ1bmN0aW9uYWwgCiAqIGZvcm1zIHdpdGggYm90aCwgSmF2YXNjcmlwdCBhbmQgc2VydmVyLXNpZGUgdmFsaWRhdGlvbi4KICovCmNsYXNzIH5hbGlhc34KewoKICAgIC8vIFByb3BlcnRpZXMKICAgIHB1YmxpYyAkYWxsb3dfcG9zdF92YWx1ZXMgPSAxOwoKLyoqCiAqIERlZmluZXMgdGhlIGZvcm0gZmllbGRzIGluY2x1ZGVkIHdpdGhpbiB0aGUgSFRNTCBmb3JtLgogKiAKICogICBAcGFyYW0gYXJyYXkgJGRhdGEgQW4gYXJyYXkgb2YgYWxsIGF0dHJpYnV0ZXMgc3BlY2lmaWVkIHdpdGhpbiB0aGUgZTpmdW5jdGlvbiB0YWcgdGhhdCBjYWxsZWQgdGhlIGZvcm0uIAogKgogKiAgIEByZXR1cm4gYXJyYXkgS2V5cyBvZiB0aGUgYXJyYXkgYXJlIHRoZSBuYW1lcyBvZiB0aGUgZm9ybSBmaWVsZHMuICBWYWx1ZXMgb2YgdGhlIGFycmF5IGFyZSBhcnJheXMgdGhhdCBzcGVjaWZ5IHRoZSBhdHRyaWJ1dGVzIG9mIHRoZSBmb3JtIGZpZWxkLiAgUmVmZXIgdG8gZG9jdW1lbnRhdGlvbiBmb3IgZGV0YWlscy4KICovCnB1YmxpYyBmdW5jdGlvbiBnZXRfZmllbGRzKGFycmF5ICRkYXRhID0gYXJyYXkoKSk6YXJyYXkKewoKICAgIC8vIFNldCBmb3JtIGZpZWxkcwogICAgJGZvcm1fZmllbGRzID0gYXJyYXkoIAogICAgICAgICduYW1lJyA9PiBhcnJheSgnZmllbGQnID0+ICd0ZXh0Ym94JywgJ2xhYmVsJyA9PiAnWW91ciBGdWxsIE5hbWUnLCAncmVxdWlyZWQnID0+IDEsICdwbGFjZWhvbGRlcicgPT4gJ0VudGVyIHlvdXIgbmFtZS4uLicpCiAgICApOwoKICAgIC8vIFJldHVybgogICAgcmV0dXJuICRmb3JtX2ZpZWxkczsKCn0KCi8qKgogKiBHZXQgdmFsdWVzIGZvciBhIHJlY29yZC4KICoKICogTWV0aG9kIGlzIGNhbGxlZCBpZiBhICdyZWNvcmRfaWQnIGF0dHJpYnV0ZSBleGlzdHMgd2l0aGluIHRoZSAKICogYTpmdW5jdGlvbiB0YWcgdGhhdCBjYWxscyB0aGUgZm9ybS4gIFdpbGwgcmV0cmlldmUgdGhlIHZhbHVlcyBmcm9tIHRoZSAKICogZGF0YWJhc2UgdG8gcG9wdWxhdGUgdGhlIGZvcm0gZmllbGRzIHdpdGguCiAqCiAqICAgQHBhcmFtIHN0cmluZyAkcmVjb3JkX2lkIFRoZSB2YWx1ZSBvZiB0aGUgJ3JlY29yZF9pZCcgYXR0cmlidXRlIGZyb20gdGhlIGU6ZnVuY3Rpb24gdGFnLgogKgogKiAgIEByZXR1cm4gYXJyYXkgQW4gYXJyYXkgb2Yga2V5LXZhbHVlIHBhaXJzIGNvbnRhaW5nIHRoZSB2YWx1ZXMgb2YgdGhlIGZvcm0gZmllbGRzLgogKi8KcHVibGljIGZ1bmN0aW9uIGdldF9yZWNvcmQoc3RyaW5nICRyZWNvcmRfaWQpOmFycmF5IAp7CgogICAgLy8gR2V0IHJlY29yZAogICAgJHJvdyA9IGFycmF5KCk7CgogICAgLy8gUmV0dXJuCiAgICByZXR1cm4gJHJvdzsKCn0KCi8qKgogKiBBZGRpdGlvbmFsIGZvcm0gdmFsaWRhdGlvbi4KICogCiAqIEFsbG93cyBmb3IgYWRkaXRpb25hbCB2YWxpZGF0aW9uIG9mIHRoZSBzdWJtaXR0ZWQgZm9ybS4gIAogKiBUaGUgc3RhbmRhcmQgc2VydmVyLXNpZGUgdmFsaWRhdGlvbiBjaGVja3MgYXJlIGNhcnJpZWQgb3V0LCBhdXRvbWF0aWNhbGx5IGFzIAogKiBkZXNpZ25hdGVkIGluIHRoZSAkZm9ybV9maWVsZHMgZGVmaW5lZCBmb3IgdGhpcyBmb3JtLiAgSG93ZXZlciwgdGhpcyAKICogYWxsb3dzIGFkZGl0aW9uYWwgdmFsaWRhdGlvbiBpZiB3YXJyYW50ZWQuCiAqCiAqICAgICBAcGFyYW0gYXJyYXkgJGRhdGEgQW55IGFycmF5IG9mIGRhdGEgcGFzc2VkIHRvIHRoZSByZWdpc3RyeTo6dmFsaWRhdGVfZm9ybSgpIG1ldGhvZC4gIFVzZWQgdG8gdmFsaWRhdGUgYmFzZWQgb24gZXhpc3RpbmcgcmVjb3JkcyAvIHJvd3MgKGVnLiBkdXBsb2NhdGUgdXNlcm5hbWUgY2hlY2ssIGJ1dCBkb24ndCBpbmNsdWRlIHRoZSBjdXJyZW50IHVzZXIpLgogKi8KcHVibGljIGZ1bmN0aW9uIHZhbGlkYXRlKGFycmF5ICRkYXRhID0gYXJyYXkoKSkgCnsKCiAgICAvLyBBZGRpdGlvbmFsIHZhbGlkYXRpb24gY2hlY2tzCgp9Cgp9Cgo=',
    'htmlfunc' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxodG1sZnVuYzsKCnVzZSBhcGV4XGFwcDsKdXNlIGFwZXhcc2VydmljZXNcZGI7CnVzZSBhcGV4XHNlcnZpY2VzXGRlYnVnOwoKLyoqCiAqIENsYXNzIHRvIGhhbmRsZSB0aGUgSFRNTCBmdW5jdGlvbiwgd2hpY2ggcmVwbGFjZXMgCiAqIHRoZSA8YTpmdW5jdGlvbj4gdGFncyB3aXRoaW4gdGVtcGxhdGVzIHRvIGFueXRoaW5nIAogKiB5b3Ugd2lzaC4KICovCmNsYXNzIH5hbGlhc34KewoKLyoqCiAqIFJlcGxhY2VzIHRoZSBjYWxsaW5nIDxlOmZ1bmN0aW9uPiB0YWcgd2l0aCB0aGUgcmVzdWx0aW5nIAogKiBzdHJpbmcgb2YgdGhpcyBmdW5jdGlvbi4KICogCiAqICAgQHBhcmFtIHN0cmluZyAkaHRtbCBUaGUgY29udGVudHMgb2YgdGhlIFRQTCBmaWxlLCBpZiBleGlzdHMsIGxvY2F0ZWQgYXQgL3ZpZXdzL2h0bWxmdW5jLzxwYWNrYWdlPi88YWxpYXM+LnRwbAogKiAgIEBwYXJhbSBhcnJheSAkZGF0YSBUaGUgYXR0cmlidXRlcyB3aXRoaW4gdGhlIGNhbGxpbmcgZTpmdW5jdGlvbj4gdGFnLgogKgogKiAgIEByZXR1cm4gc3RyaW5nIFRoZSByZXN1bHRpbmcgSFRNTCBjb2RlLCB3aGljaCB0aGUgPGU6ZnVuY3Rpb24+IHRhZyB3aXRoaW4gdGhlIHRlbXBsYXRlIGlzIHJlcGxhY2VkIHdpdGguCiAqLwpwdWJsaWMgZnVuY3Rpb24gcHJvY2VzcyhzdHJpbmcgJGh0bWwsIGFycmF5ICRkYXRhID0gYXJyYXkoKSk6c3RyaW5nCnsKCgogICAgLy8gUmV0dXJuCiAgICByZXR1cm4gJGh0bWw7Cgp9Cgp9Cgo=',
    'lib' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlfjsKCnVzZSBhcGV4XGFwcDsKdXNlIGFwZXhcc2VydmljZXNcZGI7CnVzZSBhcGV4XHNlcnZpY2VzXGRlYnVnOwoKLyoqCiAqIEJsYW5rIGxpYnJhcnkgZmlsZSB3aGVyZSB5b3UgY2FuIGFkZCAKICogYW55IGFuZCBhbGwgbWV0aG9kcyAvIHByb3BlcnRpZXMgeW91IGRlc2lyZS4KICovCmNsYXNzIH5hbGlhc34KewoKICAgIC8qKgogICAgICogQEluamVjdAogICAgICogQHZhciBhcHAKICAgICAqLwogICAgcHJpdmF0ZSAkYXBwOwoKCn0KCg==',
    'modal' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxtb2RhbDsKCnVzZSBhcGV4XGFwcDsKdXNlIGFwZXhcc2VydmljZXNcZGI7CnVzZSBhcGV4XHNlcnZpY2VzXGRlYnVnOwoKLyoqCiAqIENsYXNzIHRoYXQgaGFuZGxlcyB0aGUgbW9kYWwgLS0gdGhlIGNlbnRlciAKICogcG9wLXVwIHBhbmVsLgogKi8KY2xhc3MgfmFsaWFzfgp7CgovKioKICoqIFNob3cgdGhlIG1vZGFsIGJveC4gIFVzZWQgdG8gZ2F0aGVyIGFueSAKICogbmVjZXNzYXJ5IGRhdGFiYXNlIGluZm9ybWF0aW9uLCBhbmQgYXNzaWduIHRlbXBsYXRlIHZhcmlhYmxlcywgZXRjLgogKi8KCnB1YmxpYyBmdW5jdGlvbiBzaG93KCkKewoKCn0KCn0KCg==',
    'tabcontrol' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflx0YWJjb250cm9sOwoKdXNlIGFwZXhcYXBwOwp1c2UgYXBleFxzZXJ2aWNlc1xkYjsKdXNlIGFwZXhcc2VydmljZXNcZGVidWc7CgovKioKICogQ2xhc3MgdGhhdCBoYW5kbGVzIHRoZSB0YWIgY29udHJvbCwgYW5kIGlzIGV4ZWN1dGVkIAogKiBldmVyeSB0aW1lIHRoZSB0YWIgY29udHJvbCBpcyBkaXNwbGF5ZWQuCiAqLwpjbGFzcyB+YWxpYXN+CnsKCiAgICAvLyBEZWZpbmUgdGFiIHBhZ2VzCiAgICBwdWJsaWMgJHRhYnBhZ2VzID0gYXJyYXkoCiAgICAgICAgJ2dlbmVyYWwnID0+ICdHZW5lcmFsIFNldHRpbmdzZScgCiAgICApOwoKLyoqCiAqIFByb2Nlc3MgdGhlIHRhYiBjb250cm9sLgogKgogKiBJcyBleGVjdXRlZCBldmVyeSB0aW1lIHRoZSB0YWIgY29udHJvbCBpcyBkaXNwbGF5ZWQsIAogKiBpcyB1c2VkIHRvIHBlcmZvcm0gYW55IGFjdGlvbnMgc3VibWl0dGVkIHdpdGhpbiBmb3JtcyAKICogb2YgdGhlIHRhYiBjb250cm9sLCBhbmQgbWFpbmx5IHRvIHJldHJpZXZlIGFuZCBhc3NpZ24gdmFyaWFibGVzIAogKiB0byB0aGUgdGVtcGxhdGUgZW5naW5lLgogKgogKiAgICAgQHBhcmFtIGFycmF5ICRkYXRhIFRoZSBhdHRyaWJ1dGVzIGNvbnRhaW5lZCB3aXRoaW4gdGhlIDxlOmZ1bmN0aW9uPiB0YWcgdGhhdCBjYWxsZWQgdGhlIHRhYiBjb250cm9sLgogKi8KcHVibGljIGZ1bmN0aW9uIHByb2Nlc3MoYXJyYXkgJGRhdGEpIAp7CgoKfQoKfQoK',
    'tabpage' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflx0YWJjb250cm9sXH5wYXJlbnR+OwoKdXNlIGFwZXhcYXBwOwp1c2UgYXBleFxzZXJ2aWNlc1xkYjsKdXNlIGFwZXhcc2VydmljZXNcZGVidWc7CgovKioKICogSGFuZGxlcyB0aGUgc3BlY2lmaWNzIG9mIHRoZSBvbmUgdGFiIHBhZ2UsIGFuZCBpcyAKICogZXhlY3V0ZWQgZXZlcnkgdGltZSB0aGUgdGFiIHBhZ2UgaXMgZGlzcGxheWVkLgogKi8KY2xhc3MgfmFsaWFzfiBleHRlbmRzIFxhcGV4XGFic3RyYWN0c1x0YWJwYWdlCnsKCiAgICAvLyBQYWdlIHZhcmlhYmxlcwogICAgcHVibGljICRwb3NpdGlvbiA9ICdib3R0b20nOwogICAgcHVibGljICRuYW1lID0gJ35hbGlhc191Y34nOwoKLyoqCiAqIFByb2Nlc3MgdGhlIHRhYiBwYWdlLgogKgogKiBFeGVjdXRlcyBldmVyeSB0aW1lIHRoZSB0YWIgY29udHJvbCBpcyBkaXNwbGF5ZWQsIGFuZCB1c2VkIAogKiB0byBleGVjdXRlIGFueSBuZWNlc3NhcnkgYWN0aW9ucyBmcm9tIGZvcm1zIGZpbGxlZCBvdXQgCiAqIG9uIHRoZSB0YWIgcGFnZSwgYW5kIG1pYW5seSB0byB0cmVpZXZlIHZhcmlhYmxlcyBhbmQgYXNzaWduIAogKiB0aGVtIHRvIHRoZSB0ZW1wbGF0ZS4KICoKICogICAgIEBwYXJhbSBhcnJheSAkZGF0YSBUaGUgYXR0cmlidXRlcyBjb250YWluZCB3aXRoaW4gdGhlIDxlOmZ1bmN0aW9uPiB0YWcgdGhhdCBjYWxsZWQgdGhlIHRhYiBjb250cm9sCiAqLwpwdWJsaWMgZnVuY3Rpb24gcHJvY2VzcyhhcnJheSAkZGF0YSA9IGFycmF5KCkpIAp7CgoKfQoKfQoK',
    'table' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflx0YWJsZTsKCnVzZSBhcGV4XGFwcDsKdXNlIGFwZXhcc2VydmljZXNcZGI7CnVzZSBhcGV4XHNlcnZpY2VzXGRlYnVnOwoKCi8qKgogKiBIYW5kbGVzIHRoZSB0YWJsZSBpbmNsdWRpbmcgb2J0YWluaW5nIHRoZSByb3dzIHRvIAogKiBkaXNwbGF5LCB0b3RhbCByb3dzIGluIHRoZSB0YWJsZSwgZm9ybWF0dGluZyBvZiBjZWxscywgZXRjLgogKi8KY2xhc3MgfmFsaWFzfgp7CgogICAgLy8gQ29sdW1ucwogICAgcHVibGljICRjb2x1bW5zID0gYXJyYXkoCiAgICAgICAgJ2lkJyA9PiAnSUQnCiAgICApOwoKICAgIC8vIFNvcnRhYmxlIGNvbHVtbnMKICAgIHB1YmxpYyAkc29ydGFibGUgPSBhcnJheSgnaWQnKTsKCiAgICAvLyBPdGhlciB2YXJpYWJsZXMKICAgIHB1YmxpYyAkcm93c19wZXJfcGFnZSA9IDI1OwogICAgcHVibGljICRoYXNfc2VhcmNoID0gZmFsc2U7CgogICAgLy8gRm9ybSBmaWVsZCAobGVmdC1tb3N0IGNvbHVtbikKICAgIHB1YmxpYyAkZm9ybV9maWVsZCA9ICdjaGVja2JveCc7CiAgICBwdWJsaWMgJGZvcm1fbmFtZSA9ICd+YWxpYXN+X2lkJzsKICAgIHB1YmxpYyAkZm9ybV92YWx1ZSA9ICdpZCc7IAoKICAgIC8vIERlbGV0ZSBidXR0b24KICAgIHB1YmxpYyAkZGVsZXRlX2J1dHRvbiA9ICdEZWxldGUgQ2hlY2tlZCB+YWxpYXNfdWN+cyc7CiAgICBwdWJsaWMgJGRlbGV0ZV9kYnRhYmxlID0gJyc7CiAgICBwdWJsaWMgJGRlbGV0ZV9kYmNvbHVtbiA9ICcnOwoKLyoqCiAqIFBhcnNlIGF0dHJpYnV0ZXMgd2l0aGluIDxhOmZ1bmN0aW9uPiB0YWcuCiAqCiAqIFBhc3NlcyB0aGUgYXR0cmlidXRlcyBjb250YWluZWQgd2l0aGluIHRoZSA8ZTpmdW5jdGlvbj4gdGFnIHRoYXQgY2FsbGVkIHRoZSB0YWJsZS4KICogVXNlZCBtYWlubHkgdG8gc2hvdy9oaWRlIGNvbHVtbnMsIGFuZCByZXRyaWV2ZSBzdWJzZXRzIG9mIAogKiBkYXRhIChlZy4gc3BlY2lmaWMgcmVjb3JkcyBmb3IgYSB1c2VyIElEIykuCiAqIAogKCAgICAgQHBhcmFtIGFycmF5ICRkYXRhIFRoZSBhdHRyaWJ1dGVzIGNvbnRhaW5lZCB3aXRoaW4gdGhlIDxlOmZ1bmN0aW9uPiB0YWcgdGhhdCBjYWxsZWQgdGhlIHRhYmxlLgogKi8KcHVibGljIGZ1bmN0aW9uIGdldF9hdHRyaWJ1dGVzKGFycmF5ICRkYXRhID0gYXJyYXkoKSkKewoKfQoKLyoqCiAqIEdldCB0b3RhbCByb3dzLgogKgogKiBHZXQgdGhlIHRvdGFsIG51bWJlciBvZiByb3dzIGF2YWlsYWJsZSBmb3IgdGhpcyB0YWJsZS4KICogVGhpcyBpcyB1c2VkIHRvIGRldGVybWluZSBwYWdpbmF0aW9uIGxpbmtzLgogKiAKICogICAgIEBwYXJhbSBzdHJpbmcgJHNlYXJjaF90ZXJtIE9ubHkgYXBwbGljYWJsZSBpZiB0aGUgQUpBWCBzZWFyY2ggYm94IGhhcyBiZWVuIHN1Ym1pdHRlZCwgYW5kIGlzIHRoZSB0ZXJtIGJlaW5nIHNlYXJjaGVkIGZvci4KICogICAgIEByZXR1cm4gaW50IFRoZSB0b3RhbCBudW1iZXIgb2Ygcm93cyBhdmFpbGFibGUgZm9yIHRoaXMgdGFibGUuCiAqLwpwdWJsaWMgZnVuY3Rpb24gZ2V0X3RvdGFsKHN0cmluZyAkc2VhcmNoX3Rlcm0gPSAnJyk6aW50IAp7CgogICAgLy8gR2V0IHRvdGFsCiAgICBpZiAoJHNlYXJjaF90ZXJtICE9ICcnKSB7IAogICAgICAgICR0b3RhbCA9IERCOjpnZXRfZmllbGQoIlNFTEVDVCBjb3VudCgqKSBGUk9NIH5wYWNrYWdlfl9+YWxpYXN+IFdIRVJFIHNvbWVfY29sdW1uIExJS0UgJWxzIiwgJHNlYXJjaF90ZXJtKTsKICAgIH0gZWxzZSB7IAogICAgICAgICR0b3RhbCA9IERCOjpnZXRfZmllbGQoIlNFTEVDVCBjb3VudCgqKSBGUk9NIH5wYWNrYWdlfl9+YWxpYXN+Iik7CiAgICB9CiAgICBpZiAoJHRvdGFsID09ICcnKSB7ICR0b3RhbCA9IDA7IH0KCiAgICAvLyBSZXR1cm4KICAgIHJldHVybiAoaW50KSAkdG90YWw7Cgp9CgovKioKICogR2V0IHJvd3MgdG8gZGlzcGxheQogKgogKiBHZXRzIHRoZSBhY3R1YWwgcm93cyB0byBkaXNwbGF5IHRvIHRoZSB3ZWIgYnJvd3Nlci4KICogVXNlZCBmb3Igd2hlbiBpbml0aWFsbHkgZGlzcGxheWluZyB0aGUgdGFibGUsIHBsdXMgQUpBWCBiYXNlZCBzZWFyY2gsIAogKiBzb3J0LCBhbmQgcGFnaW5hdGlvbi4KICoKICogICAgIEBwYXJhbSBpbnQgJHN0YXJ0IFRoZSBudW1iZXIgdG8gc3RhcnQgcmV0cmlldmluZyByb3dzIGF0LCB1c2VkIHdpdGhpbiB0aGUgTElNSVQgY2xhdXNlIG9mIHRoZSBTUUwgc3RhdGVtZW50LgogKiAgICAgQHBhcmFtIHN0cmluZyAkc2VhcmNoX3Rlcm0gT25seSBhcHBsaWNhYmxlIGlmIHRoZSBBSkFYIGJhc2VkIHNlYXJjaCBiYXNlIGlzIHN1Ym1pdHRlZCwgYW5kIGlzIHRoZSB0ZXJtIGJlaW5nIHNlYXJjaGVkIGZvcm0uCiAqICAgICBAcGFyYW0gc3RyaW5nICRvcmRlcl9ieSBNdXN0IGhhdmUgYSBkZWZhdWx0IHZhbHVlLCBidXQgY2hhbmdlcyB3aGVuIHRoZSBzb3J0IGFycm93cyBpbiBjb2x1bW4gaGVhZGVycyBhcmUgY2xpY2tlZC4gIFVzZWQgd2l0aGluIHRoZSBPUkRFUiBCWSBjbGF1c2UgaW4gdGhlIFNRTCBzdGF0ZW1lbnQuCiAqCiAqICAgICBAcmV0dXJuIGFycmF5IEFuIGFycmF5IG9mIGFzc29jaWF0aXZlIGFycmF5cyBnaXZpbmcga2V5LXZhbHVlIHBhaXJzIG9mIHRoZSByb3dzIHRvIGRpc3BsYXkuCiAqLwpwdWJsaWMgZnVuY3Rpb24gZ2V0X3Jvd3MoaW50ICRzdGFydCA9IDAsIHN0cmluZyAkc2VhcmNoX3Rlcm0gPSAnJywgc3RyaW5nICRvcmRlcl9ieSA9ICdpZCBhc2MnKTphcnJheSAKewoKICAgIC8vIEdldCByb3dzCiAgICBpZiAoJHNlYXJjaF90ZXJtICE9ICcnKSB7IAogICAgICAgICRyb3dzID0gREI6OnF1ZXJ5KCJTRUxFQ1QgKiBGUk9NIH5wYWNrYWdlfl9+YWxpYXN+IFdIRVJFIHNvbWVfY29sdW1uIExJS0UgJWxzIE9SREVSIEJZICRvcmRlcl9ieSBMSU1JVCAkc3RhcnQsJHRoaXMtPnJvd3NfcGVyX3BhZ2UiLCAkc2VhcmNoX3Rlcm0pOwogICAgfSBlbHNlIHsgCiAgICAgICAgJHJvd3MgPSBEQjo6cXVlcnkoIlNFTEVDVCAqIEZST00gfnBhY2thZ2V+X35hbGlhc34gT1JERVIgQlkgJG9yZGVyX2J5IExJTUlUICRzdGFydCwkdGhpcy0+cm93c19wZXJfcGFnZSIpOwogICAgfQoKICAgIC8vIEdvIHRocm91Z2ggcm93cwogICAgJHJlc3VsdHMgPSBhcnJheSgpOwogICAgZm9yZWFjaCAoJHJvd3MgYXMgJHJvdykgeyAKICAgICAgICBhcnJheV9wdXNoKCRyZXN1bHRzLCAkdGhpcy0+Zm9ybWF0X3Jvdygkcm93KSk7CiAgICB9CgogICAgLy8gUmV0dXJuCiAgICByZXR1cm4gJHJlc3VsdHM7Cgp9CgovKioKICogRm9ybWF0IGEgc2luZ2xlIHJvdy4KICoKICogUmV0cmlldmVzIHJhdyBkYXRhIGZyb20gdGhlIGRhdGFiYXNlLCB3aGljaCBtdXN0IGJlIAogKiBmb3JtYXR0ZWQgaW50byB1c2VyIHJlYWRhYmxlIGZvcm1hdCAoZWcuIGZvcm1hdCBhbW91bnRzLCBkYXRlcywgZXRjLikuCiAqCiAqICAgICBAcGFyYW0gYXJyYXkgJHJvdyBUaGUgcm93IGZyb20gdGhlIGRhdGFiYXNlLgogKgogKiAgICAgQHJldHVybiBhcnJheSBUaGUgcmVzdWx0aW5nIGFycmF5IHRoYXQgc2hvdWxkIGJlIGRpc3BsYXllZCB0byB0aGUgYnJvd3Nlci4KICovCnB1YmxpYyBmdW5jdGlvbiBmb3JtYXRfcm93KGFycmF5ICRyb3cpOmFycmF5IAp7CgogICAgLy8gRm9ybWF0IHJvdwoKCiAgICAvLyBSZXR1cm4KICAgIHJldHVybiAkcm93OwoKfQoKfQoK',
    'test' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSB0ZXN0c1x+cGFja2FnZX47Cgp1c2UgYXBleFxhcHA7CnVzZSBhcGV4XHNlcnZpY2VzXGRiOwp1c2UgYXBleFxzZXJ2aWNlc1xkZWJ1ZzsKdXNlIGFwZXhcYXBwXHRlc3RzXHRlc3Q7CgoKLyoqCiAqIEFkZCBhbnkgbmVjZXNzYXJ5IHBocFVuaXQgdGVzdCBtZXRob2RzIGludG8gdGhpcyBjbGFzcy4gIFlvdSBtYXkgZXhlY3V0ZSBhbGwgCiAqIHRlc3RzIGJ5IHJ1bm5pbmc6ICBwaHAgYXBleC5waHAgdGVzdCB+cGFja2FnZX4KICovCmNsYXNzIHRlc3RffmFsaWFzfiBleHRlbmRzIHRlc3QKewoKLyoqCiAqIHNldFVwCiAqLwpwdWJsaWMgZnVuY3Rpb24gc2V0VXAoKTp2b2lkCnsKCiAgICAvLyBHZXQgYXBwCiAgICBpZiAoISRhcHAgPSBhcHA6OmdldF9pbnN0YW5jZSgpKSB7IAogICAgICAgICRhcHAgPSBuZXcgYXBwKCd0ZXN0Jyk7CiAgICB9Cgp9CgovKioKICogdGVhckRvd24KICovCnB1YmxpYyBmdW5jdGlvbiB0ZWFyRG93bigpOnZvaWQKewoKfQoKCgp9Cgo=',
    'worker' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflx3b3JrZXI7Cgp1c2UgYXBleFxhcHA7CnVzZSBhcGV4XHNlcnZpY2VzXGRiOwp1c2UgYXBleFxzZXJ2aWNlc1xkZWJ1ZzsKdXNlIGFwZXhcYXBwXGludGVyZmFjZXNcbXNnXEV2ZW50TWVzc2FnZUludGVyZmFjZTsKCi8qKgogKiBDbGFzcyB0aGF0IGhhbmRsZXMgYSB3b3JrZXIgLyBsaXN0ZW5lciBjb21wb25lbnQsIHdoaWNoIGlzIAogKiB1c2VkIGZvciBvbmUtd2F5IGRpcmVjdCBhbmQgdHdvLXdheSBSUEMgbWVzc2FnZXMgdmlhIFJhYmJpdE1RLCAKICogYW5kIHNob3VsZCBiZSB1dGlsaXplZCBhIGdvb2QgZGVhbC4KICovCmNsYXNzIH5hbGlhc34gaW1wbGVtZW50cyBFdmVudE1lc3NhZ2VJbnRlcmZhY2UKewoKCgp9Cgo='
    );


/**
 * Create a new component.  Used via the apex.php script, to create a new 
 * component including necessary files. 
 *
 * @param string $type The type of component (template, worker, lib, etc.)
 * @param string $comp_alias Alias of the component in Apex format (ie. PACKAGE:[PARENT:]ALIAS
 * @param string $owner Optional owner, only required for a few components (controller, tab_page, worker)
 */
public static function create(string $type, string $comp_alias, string $owner = '')
{ 

    // Perform necessary checks
    list($alias, $parent, $package, $value, $owner) = self::add_checks($type, $comp_alias, '', $owner);

    // Create view, if needed
    if ($type == 'view') { 
        return self::create_view($alias, $owner);
    }

    // Debug
    debug::add(4, fmsg("Starting to create component, type: {1}, package: {2}, parent: {3}, alias: {4}, owner: {5}", $type, $package, $parent, $alias, $owner), __FILE__, __LINE__);

    // Get PHP filename
    $php_file = components::get_file($type, $alias, $package, $parent);
    if ($php_file == '') { 
        throw new ComponentException('no_php_file', $type, '', $alias, $package, $parent);
    }
    $php_file = SITE_PATH . '/' . $php_file;

    // Check if PHP file exists already
    if (file_exists($php_file)) { 
        throw new ComponentException('php_file_exists', $type, '', $alias, $package, $parent);
    }

    // Create component file
    $code_type = ($type == 'controller' && $parent == '') ? 'controller_parent' : $type;
    $code = base64_decode(self::$code_templates[$code_type]);
    $code = str_replace("~package~", $package, $code);
    $code = str_replace("~parent~", $parent, $code);
    $code = str_replace("~alias~", $alias, $code);
    $code = str_replace("~alias_uc~", ucwords($alias), $code);

    // Save file
    io::create_dir(dirname($php_file));
    file_put_contents($php_file, $code);

    // Debug
    debug::add(5, fmsg("Created new PHP file for components at {1}", $php_file), __FILE__, __LINE__);

    // Save .tpl file as needed
    $tpl_file = components::get_tpl_file($type, $alias, $package, $parent);
    if ($tpl_file != '') { 
        io::create_dir(dirname(SITE_PATH . '/' . $tpl_file));
        file_put_contents(SITE_PATH . '/' . $tpl_file, '');
    }

// Create tab control directory
    if ($type == 'tabcontrol') { 
        io::create_dir(SITE_PATH . '/src/' . $package . '/tabcontrol/' . $alias);
        io::create_dir(SITE_PATH . '/views/tabpage/' . $package . '/' . $alias);
    }

    // Add component
    self::add($type, $comp_alias, $value, 0, $owner);

    // Add crontab job, if needed
    if ($type == 'cron') { 
        self::add_crontab($package, $alias);
    }

    // Debug
debug::add(4, fmsg("Successfully created new component, type: {1}, package: {2}, parent: {3}, alias: {4}", $type, $package, $parent, $alias), __FILE__, __LINE__);

    // Return
    return array($type, $alias, $package, $parent);

}

/**
 * Create a new template, including necessary files.  Used by the apex.php 
 * script. 
 */
protected static function create_view(string $uri, string $package)
{ 

    // Check uri
    if ($uri == '' || preg_match("/\s/", $uri)) { 
        throw new ComponentException('invalid_template_uri', 'template', '', $uri);
    }

    // Debug
    debug::add(4, fmsg("Starting to create new template at, {1}", $uri), __FILE__, __LINE__);

    // Set filenames
    $uri = trim(strtolower($uri), '/');
    $tpl_file = SITE_PATH . '/views/tpl/' . $uri . '.tpl';
    $php_file = SITE_PATH . '/views/php/' . $uri . '.php';

    // Create directories as needed
    io::create_dir(dirname($tpl_file));
    io::create_dir(dirname($php_file));

    // Save files
    file_put_contents($tpl_file, '');
    file_put_contents($php_file, base64_decode('PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XHZpZXdzOwoKdXNlIGFwZXhcYXBwOwp1c2UgYXBleFxzZXJ2aWNlc1xkYjsKdXNlIGFwZXhcc2VydmljZXNcdGVtcGxhdGU7CnVzZSBhcGV4XHNlcnZpY2VzXGRlYnVnOwoKLyoqCiAqIEFsbCBjb2RlIGJlbG93IHRoaXMgbGluZSBpcyBhdXRvbWF0aWNhbGx5IGV4ZWN1dGVkIHdoZW4gdGhpcyB0ZW1wbGF0ZSBpcyB2aWV3ZWQsIAogKiBhbmQgdXNlZCB0byBwZXJmb3JtIGFueSBuZWNlc3NhcnkgdGVtcGxhdGUgc3BlY2lmaWMgYWN0aW9ucy4KICovCgoKCg=='));

    // Add component
    self::add('view', $uri, '', 0, $package);

    // Debug
    debug::add(4, fmsg("Successfully created new template at, {1}", $uri), __FILE__, __LINE__);

    // Return
    return array('view', $uri, $package, '');

}

/**
 * Add component to database 
 *
 * Add a new component into the database.  This will not actually create the 
 * necessary PHP / TPL files, and instead will only add the necessary row(s) 
 * into the database. 
 *
 * @param string $type The type of component (htmlfunc, worker, hash, etc.)
 * @param string $comp_alias Alias of component in standard Apex format (PACKAGE:[PARENT:]ALIAS)
 * @param string $value Only required for a few components such as 'config', and is the value of the component
 * @param string $owner Only needed for controller and tabpage components, and is the owner package of the component.
 */
public static function add(string $type, string $comp_alias, string $value = '', int $order_num = 0, string $owner = '')
{ 

    // Perform necessary checks
    list($alias, $parent, $package, $value, $owner) = self::add_checks($type, $comp_alias, $value, $owner);

    // Update component, if needed
    if ($row = db::get_row("SELECT * FROM internal_components WHERE type = %s AND package = %s AND parent = %s AND alias = %s", $type, $package, $parent, $alias)) { 

        // Set updates
        $updates = array();
        if ($order_num > 0) { $updates['order_num'] = $order_num; }
        if ($value != '' && $type != 'config') { 
            $updates['value'] = $value;
        }

        // Debug
        debug::add(5, fmsg("Updating existing component, type: {1}, package: {2}, parent: {3}, alias: {4}", $type, $package, $parent, $alias), __FILE__, __LINE__);

        // Update database
        if (count($updates) > 0) { 
            db::update('internal_components', $updates, "id = %i", $row['id']);
        }

        // Reorder tab control, if needed
        if ($type == 'tabpage') { 
            $pkg_client = new package_client($package);
            $pkg_client->reorder_tab_control($parent, $package);
        }

        // Return
        return true;
    }

    // Debug
    debug::add(4, fmsg("Adding new component to database, type: {1}, package: {2}, parent: {3}, alias: {4}", $type, $package, $parent, $alias), __FILE__, __LINE__);

    // Add component to DB
    db::insert('internal_components', array(
        'order_num' => $order_num,
        'type' => $type,
        'owner' => $owner,
        'package' => $package,
        'parent' => $parent,
        'alias' => $alias,
        'value' => $value)
    );
    $component_id = db::insert_id();

    // Add crontab job, if needed
    if ($type == 'cron') { 
        self::add_crontab($package, $alias);
    }

    // Add to redis
    redis::sadd('config:components', implode(":", array($type, $package, $parent, $alias)));

    // Add to redis -- components_packages
    $chk = $type . ':' . $alias;
    if (!$value = redis::hget('config:components_package', $chk)) { 
        redis::hset('config:components_package', $chk, $package);
    } elseif ($value != $package) { 
        redis::hset('config:components_package', $chk, 2);
    }

}

/**
 * Perform all necessary checks on a component before adding it to the 
 * database. 
 *
 * @param string $type The type of component (htmlfunc, worker, hash, etc.)
 * @param string $comp_alias Alias of component in standard Apex format (PACKAGE:[PARENT:]ALIAS)
 * @param string $value Only required for a few components such as 'config', and is the value of the component
 * @param string $owner Only needed for controller and tabpage components, and is the owner package of the component.
 */
protected static function add_checks(string $type, string $comp_alias, string $value = '', string $owner = '')
{ 

    // Split alias
    $vars = explode(":", strtolower($comp_alias));
    if ($type != 'view' && count($vars) < 2 || count($vars) > 3) { 
        throw new ComponentException('invalid_comp_alias', $type, $comp_alias);
    }

    // Set component vars
    $package = $type == 'view' ? $owner : array_shift($vars);
    $parent = isset($vars[1]) ? $vars[0] : '';
    $alias = $vars[1] ?? $vars[0];

    // Check parent
    if ($parent == '' && ($type == 'tabpage' || $type == 'hash_var')) { 
        throw new ComponentException('no_parent', $type, $comp_alias);
    }

    // Ensure parent exists
    if ($parent != '') { 

        // Get parent type
        if ($type == 'tabpage') { $parent_type = 'tabcontrol'; }
        elseif ($type == 'hash_var') { $parent_type = 'hash'; }
        else { $parent_type = $type; }

        // Check parent
        if (!components::check($parent_type, $package . ':' . $parent)) { 
            throw new ComponentException('parent_not_exists', $type, $comp_alias);
        }
    }

    // Check owner
    if ($owner == '' && (($type == 'controller' && $parent != '') || ($type == 'tabpage'))) { 
        throw new ComponentException('no_owner', $type, $comp_alias);
    } elseif ($owner == '' && $value == '' && $type == 'worker') { 
        throw new ComponentException('no_worker_routing_key');
    }

    // Set value for worker
    if ($type == 'worker' && $value == '') { 
        $value = $owner;
        $owner = '';
    }

    // Set owner
    if ($owner == '') { $owner = $package; }

    // Return
    return array($alias, $parent, $package, $value, $owner);

}

/**
 * Adds a crontab job to the 'internal_crontab' table of the database 
 *
 * @param string $package The package the cron job is being added to.
 * @param string $alias The alias of the crontab job.
 */
protected static function add_crontab(string $package, string $alias)
{ 

    // Check if crontab job already exists
    if ($row = db::get_row("SELECT * FROM internal_crontab WHERE package = %s AND alias = %s", $package, $alias)) { 
        return true;
    }

    // Load file
    if (!$cron = components::load('cron', $alias, $package)) { 
        throw new ComponentException('no_load', 'cron', '', $alias, $package);
    }

    // Get date
    $next_date = date::add_interval($cron->time_interval, time(), false);
    $name = isset($cron->name) ? $cron->name : $alias;

    // Add to database
    db::insert('internal_crontab', array(
        'time_interval' => $cron->time_interval,
        'nextrun_time' => $next_date,
        'package' => $package,
        'alias' => $alias,
        'display_name' => $name)
    );

}

/**
 * Deletes a component from the database, including corresponding file(s). 
 *
 * @param string $type The type of component being deleted (eg. 'config', 'htmlfunc', etc.).
 * @param string $comp_alias Alias of component to delete in standard Apex format (ie. PACKAGE:[PARENT:]ALIAS)
 *
 * @return bool Whether or not the operation was successful.
 */
public static function remove(string $type, string $comp_alias)
{ 

    // Debug
    debug::add(4, fmsg("Deleting component, type: {1}, comp alias: {2}", $type, $comp_alias), __FILE__, __LINE__);

    // Check if component exists
    if (!list($package, $parent, $alias) = components::check($type, $comp_alias)) { 
        return true;
    }

    // Get component row
    if (!$row = db::get_row("SELECT * FROM internal_components WHERE package = %s AND type = %s AND parent = %s AND alias = %s", $package, $type, $parent, $alias)) { 
        return true;
    }

    // Delete files
    $files = components::get_all_files($type, $alias, $package, $parent);
    foreach ($files as $file) { 
        if (!file_exists(SITE_PATH . '/' . $file)) { continue; }
        @unlink(SITE_PATH . '/' . $file);

        // Remove parent directory, if empty
        $child_files = io::parse_dir(dirname(SITE_PATH . '/' . $file));
        if (count($child_files) == 0) { 
            io::remove_dir(dirname(SITE_PATH . '/' . $file));
        }
    }

    // Delete tab control directory, if needed
    if ($type == 'tabcontrol') { 
        io::remove_dir(SITE_PATH . '/src/' . $package . '/tabcontrol/' . $alias);
        io::remove_dir(SITE_PATH . '/views/tabpage/' . $package . '/' . $alias);
    }

    // Delete children, if needed
    if ($type == 'tabcontrol' || ($type == 'controller' && $parent == '')) { 
        $child_type = $type == 'tabcontrol' ? 'tabpage' : 'controller';

        // Go through child components
        $children = db::query("SELECT * FROM internal_components WHERE type = %s AND package = %s AND parent = %s", $child_type, $package, $alias);
        foreach ($children as $crow) { 
        $del_alias = $crow['package'] . ':' . $crow['parent'] . ':' . $crow['alias'];
            self::remove($crow['type'], $del_alias);
        }
    }

    // Delete from database
    db::query("DELETE FROM internal_components WHERE package = %s AND type = %s AND parent = %s AND alias = %s", $package, $type, $parent, $alias);
    if ($type == 'cron') { 
        db::query("DELETE FROM internal_crontab WHERE package = %s AND alias = %s", $package, $alias);
    }

    // Delete from redis
    redis::srem('config:components', implode(":", array($type, $package, $parent, $alias)));

    // Update redis components package as needed
    $chk = $type . ':' . $alias;
    if (redis::hget('config:components_package', $chk) != 2) { 
        redis::hdel('config:components_package', $chk);
    } else { 
        $chk_packages = db::get_column("SELECT package FROM internal_components WHERE type = %s AND alias = %s AND parent = %s", $package, $alias, $parent);
        if (count($chk_packages) == 1) { 
            redis::hset('config:components_package', $chk, $chk_packages[0]);
        }
    }

    // Delete config / hash from redis
    if ($type == 'config') { 
        redis::hdel('config', $package . ':' . $alias);
    } elseif ($type == 'hash') { 
        redis::hdel('hash', $package . ':' . $alias);
    }

    // Debug / log
    debug::add(2, fmsg("Deleted component.  owner {1}, type: {2}, package: {3}, alias {4}, parent: {5}", $package, $type, $package, $alias, $parent), __FILE__, __LINE__);

    // Return
    return true;

}


}

