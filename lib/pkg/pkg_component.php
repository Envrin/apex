<?php
declare(strict_types = 1);

namespace apex\pkg;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\ComponentException;
use apex\pkg\package_config;
use apex\core\components;
use apex\core\io;
use apex\core\date;


/**
* Handles creating, adding, deleting, and updating components 
* within packages.  Used for all package / upgrade functions.
*/
class pkg_component
{

    // Set code templates
    private static $code_templates = array(
        'ajax' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxhamF4OwoKdXNlIGFwZXhcREI7CnVzZSBhcGV4XHJlZ2lzdHJ5Owp1c2UgYXBleFxsb2c7CnVzZSBhcGV4XGRlYnVnOwoKY2xhc3MgfmFsaWFzfiBleHRlbmRzIFxhcGV4XGFqYXggCnsKCi8qKgoqIFByb2Nlc3NlcyB0aGUgQUpBWCBmdW5jdGlvbiwgYW5kIHVzZXMgdGhlIAoqIG1vZXRoZHMgd2l0aGluIHRoZSAnYXBleFxhamF4JyBjbGFzcyB0byBtb2RpZnkgdGhlIAoqIERPTSBlbGVtZW50cyB3aXRoaW4gdGhlIHdlYiBicm93c2VyLiAgU2VlIAoqIGRvY3VtZW50YXRpb24gZm9yIGR1cnRoZXIgZGV0YWlscy4KKi8KCnB1YmxpYyBmdW5jdGlvbiBwcm9jZXNzKCkKewoKICAgIC8vIFBlcmZvcm0gbmVjZXNzYXJ5IGFjdGlvbnMKCn0KCn0KCg==', 
        'autosuggest' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxhdXRvc3VnZ2VzdDsKCnVzZSBhcGV4XERCOwp1c2UgYXBleFxyZWdpc3RyeTsKdXNlIGFwZXhcbG9nOwp1c2UgYXBleFxkZWJ1ZzsKCmNsYXNzIH5hbGlhc34gZXh0ZW5kcyBcYXBleFxhYnN0cmFjdHNcYXV0b3N1Z2dlc3QKewoKLyoqCiogU2VhcmNoZXMgZGF0YWJhc2UgdXNpbmcgdGhlIGdpdmVuICR0ZXJtLCBhbmQgcmV0dXJucyBhcnJheSBvZiByZXN1bHRzLCB3aGljaCAKKiBhcmUgdGhlbiBkaXNwbGF5ZWQgd2l0aGluIHRoZSBhdXRvLXN1Z2dlc3QgLyBjb21wbGV0ZSBib3guCioKKiAgICAgQHBhcmFtIHN0cmluZyAkdGVybSBUaGUgc2VhcmNoIHRlcm0gZW50ZXJlZCBpbnRvIHRoZSB0ZXh0Ym94LgoqICAgICBAcmV0dXJuIGFycmF5IEFuIGFycmF5IG9mIGtleS12YWx1ZSBwYXJpcywga2V5cyBhcmUgdGhlIHVuaXF1ZSBJRCMgb2YgdGhlIHJlY29yZCwgYW5kIHZhbHVlcyBhcmUgZGlzcGxheWVkIGluIHRoZSBhdXRvLXN1Z2dlc3QgbGlzdC4KKi8KCnB1YmxpYyBmdW5jdGlvbiBzZWFyY2goc3RyaW5nICR0ZXJtKTphcnJheSAKewoKICAgIC8vIEdldCBvcHRpb25zCiAgICAkb3B0aW9ucyA9IGFycmF5KCk7CgoKICAgIC8vIFJldHVybgogICAgcmV0dXJuICRvcHRpb25zOwoKfQoKfQoK', 
        'controller' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxjb250cm9sbGVyXH5wYXJlbnR+OwoKdXNlIGFwZXhcREI7CnVzZSBhcGV4XHJlZ2lzdHJ5Owp1c2UgYXBleFxsb2c7CnVzZSBhcGV4XGRlYnVnOwoKY2xhc3MgfmFsaWFzfiBleHRlbmRzIFxhcGV4XH5wYWNrYWdlflxjb250cm9sbGVyXH5wYXJlbnR+CnsKCi8qKgoqIEJsYW5rIFBIUCBjbGFzcyBmb3IgdGhlIGNvbnRyb2xsZXIuICBGb3IgdGhlIAoqIGNvcnJlY3QgbWV0aG9kcyBhbmQgcHJvcGVydGllcyBmb3IgdGhpcyBjbGFzcywgcGxlYXNlIAoqIHJldmlldyB0aGUgYWJzdHJhY3QgY2xhc3MgbG9jYXRlZCBhdDoKKiAgICAgL3NyYy9+cGFja2FnZX4vY29udHJvbGxlci9+cGFja2FnZX4ucGhwCioKKi8KCgp9Cgo=', 
        'controller_parent' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxjb250cm9sbGVyOwoKYWJzdHJhY3QgY2xhc3MgfmFsaWFzfgp7CgovKioKKiBBZGQgdGhlIG5lY2Vzc2FyeSBhYnN0cmFjdCBtZXRob2RzIGFuZCAKKiBwcm9wZXJ0aWVzIGhlcmUuICBBbGwgY29udHJvbGxlcnMgb2YgdGhpcyB0eXBlIHdpbGwgZXh0ZW5kIHRoaXMgCiogY2xhc3MsIGFuZCBtdXN0IGNvbmZpcm0gdG8gdGhlIGFic3RyYWN0IGNsYXNzZXMgLyBwcm9wZXJ0aWVzIGhlcmUuCiogCiogUGxlYXNlIGJlIGtpbmQsIGFuZCBhZGQgZXh0ZW5zaXZlIGRvY3VtZW50YXRpb24gdG8gCiogdGhlIG1ldGhvZHMgLyBwcm9wZXJ0aWVzIGZvciBvdGhlciBkZXZlbG9wZXJzLgoqLwoKCn0KCg==', 
        'cron' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxjcm9uOwoKdXNlIGFwZXhcREI7CnVzZSBhcGV4XHJlZ2lzdHJ5Owp1c2UgYXBleFxsb2c7CnVzZSBhcGV4XGRlYnVnOwoKY2xhc3MgfmFsaWFzfiBleHRlbmRzIFxhcGV4XGFic3RyYWN0c1xjcm9uCnsKCiAgICAvLyBQcm9wZXJ0aWVzCiAgICBwdWJsaWMgJHRpbWVfaW50ZXJ2YWwgPSAnSTMwJzsKICAgIHB1YmxpYyAkbmFtZSA9ICd+YWxpYXN+JzsKCi8qKgoqIFByb2Nlc3NlcyB0aGUgY3JvbnRhYiBqb2IuCiovCgpwdWJsaWMgZnVuY3Rpb24gcHJvY2VzcygpCnsKCgoKfQoKfQo=', 
        'form' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxmb3JtOwoKdXNlIGFwZXhcREI7CnVzZSBhcGV4XHJlZ2lzdHJ5Owp1c2UgYXBleFxsb2c7CnVzZSBhcGV4XGRlYnVnOwoKY2xhc3MgfmFsaWFzfiBleHRlbmRzIFxhcGV4XGFic3RyYWN0c1xmb3JtCnsKCiAgICBwdWJsaWMgJGFsbG93X3Bvc3RfdmFsdWVzID0gMTsKCi8qKgoqIERlZmluZXMgdGhlIGZvcm0gZmllbGRzIGluY2x1ZGVkIHdpdGhpbiB0aGUgSFRNTCBmb3JtLgoqIAoqICAgQHBhcmFtIGFycmF5ICRkYXRhIEFuIGFycmF5IG9mIGFsbCBhdHRyaWJ1dGVzIHNwZWNpZmllZCB3aXRoaW4gdGhlIGU6ZnVuY3Rpb24gdGFnIHRoYXQgY2FsbGVkIHRoZSBmb3JtLiAKKiAgIEByZXR1cm4gYXJyYXkgS2V5cyBvZiB0aGUgYXJyYXkgYXJlIHRoZSBuYW1lcyBvZiB0aGUgZm9ybSBmaWVsZHMuCiogICAgICAgVmFsdWVzIG9mIHRoZSBhcnJheSBhcmUgYXJyYXlzIHRoYXQgc3BlY2lmeSB0aGUgYXR0cmlidXRlcyAKKiAgICAgICBvZiB0aGUgZm9ybSBmaWVsZC4gIFJlZmVyIHRvIGRvY3VtZW50YXRpb24gZm9yIGRldGFpbHMuCiovCgpwdWJsaWMgZnVuY3Rpb24gZ2V0X2ZpZWxkcyhhcnJheSAkZGF0YSA9IGFycmF5KCkpOmFycmF5CnsKCiAgICAvLyBTZXQgZm9ybSBmaWVsZHMKICAgICRmb3JtX2ZpZWxkcyA9IGFycmF5KCAKICAgICAgICAnbmFtZScgPT4gYXJyYXkoJ2ZpZWxkJyA9PiAndGV4dGJveCcsICdsYWJlbCcgPT4gJ1lvdXIgRnVsbCBOYW1lJywgJ3JlcXVpcmVkJyA9PiAxLCAncGxhY2Vob2xkZXInID0+ICdFbnRlciB5b3VyIG5hbWUuLi4nKQogICAgKTsKCiAgICAvLyBSZXR1cm4KICAgIHJldHVybiAkZm9ybV9maWVsZHM7Cgp9CgovKioKKiBNZXRob2QgaXMgY2FsbGVkIGlmIGEgJ3JlY29yZF9pZCcgYXR0cmlidXRlIGV4aXN0cyB3aXRoaW4gdGhlIAoqIGU6ZnVuY3Rpb24gdGFnIHRoYXQgY2FsbHMgdGhlIGZvcm0uICBXaWxsIHJldHJpZXZlIHRoZSB2YWx1ZXMgZnJvbSB0aGUgCiogZGF0YWJhc2UgdG8gcG9wdWxhdGUgdGhlIGZvcm0gZmllbGRzIHdpdGguCioKKiAgIEBwYXJhbSBzdHJpbmcgJHJlY29yZF9pZCBUaGUgdmFsdWUgb2YgdGhlICdyZWNvcmRfaWQnIGF0dHJpYnV0ZSBmcm9tIHRoZSBlOmZ1bmN0aW9uIHRhZy4KKiAgIEByZXR1cm4gYXJyYXkgQW4gYXJyYXkgb2Yga2V5LXZhbHVlIHBhaXJzIGNvbnRhaW5nIHRoZSB2YWx1ZXMgb2YgdGhlIGZvcm0gZmllbGRzLgoqLwoKcHVibGljIGZ1bmN0aW9uIGdldF9yZWNvcmQoc3RyaW5nICRyZWNvcmRfaWQpOmFycmF5IAp7CgogICAgLy8gR2V0IHJlY29yZAogICAgJHJvdyA9IGFycmF5KCk7CgogICAgLy8gUmV0dXJuCiAgICByZXR1cm4gJHJvdzsKCn0KCi8qKgoqIEFsbG93cyBmb3IgYWRkaXRpb25hbCB2YWxpZGF0aW9uIG9mIHRoZSBzdWJtaXR0ZWQgZm9ybS4gIAoqIFRoZSBzdGFuZGFyZCBzZXJ2ZXItc2lkZSB2YWxpZGF0aW9uIGNoZWNrcyBhcmUgY2FycmllZCBvdXQsIGF1dG9tYXRpY2FsbHkgYXMgCiogZGVzaWduYXRlZCBpbiB0aGUgJGZvcm1fZmllbGRzIGRlZmluZWQgZm9yIHRoaXMgZm9ybS4gIEhvd2V2ZXIsIHRoaXMgCiogYWxsb3dzIGFkZGl0aW9uYWwgdmFsaWRhdGlvbiBpZiB3YXJyYW50ZWQuCioKKiAgICAgQHBhcmFtIGFycmF5ICRkYXRhIEFueSBhcnJheSBvZiBkYXRhIHBhc3NlZCB0byB0aGUgcmVnaXN0cnk6OnZhbGlkYXRlX2Zvcm0oKSBtZXRob2QuICBVc2VkIAoqICAgICAgICAgdG8gdmFsaWRhdGUgYmFzZWQgb24gZXhpc3RpbmcgcmVjb3JkcyAvIHJvd3MgKGVnLiBkdXBsb2NhdGUgdXNlcm5hbWUgY2hlY2ssIGJ1dCBkb24ndCBpbmNsdWRlIHRoZSBjdXJyZW50IHVzZXIpLgoqLwoKcHVibGljIGZ1bmN0aW9uIHZhbGlkYXRlKGFycmF5ICRkYXRhID0gYXJyYXkoKSkgCnsKCiAgICAvLyBBZGRpdGlvbmFsIHZhbGlkYXRpb24gY2hlY2tzCgp9Cgp9Cgo=', 
        'htmlfunc' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxodG1sZnVuYzsKCnVzZSBhcGV4XERCOwp1c2UgYXBleFxyZWdpc3RyeTsKdXNlIGFwZXhcbG9nOwp1c2UgYXBleFxkZWJ1ZzsKCmNsYXNzIH5hbGlhc34gZXh0ZW5kcyBcYXBleFxhYnN0cmFjdHNcaHRtbGZ1bmMgCnsKCi8qKgoqIFJlcGxhY2VzIHRoZSBjYWxsaW5nIDxlOmZ1bmN0aW9uPiB0YWcgd2l0aCB0aGUgcmVzdWx0aW5nIAoqIHN0cmluZyBvZiB0aGlzIGZ1bmN0aW9uLgoqIAoqICAgQHBhcmFtIHN0cmluZyAkaHRtbCBUaGUgY29udGVudHMgb2YgdGhlIFRQTCBmaWxlLCBpZiBleGlzdHMsIGxvY2F0ZWQgYXQgL3ZpZXdzL2h0bWxmdW5jLzxwYWNrYWdlPi88YWxpYXM+LnRwbAoqICAgQHBhcmFtIGFycmF5ICRkYXRhIFRoZSBhdHRyaWJ1dGVzIHdpdGhpbiB0aGUgY2FsbGluZyBlOmZ1bmN0aW9uPiB0YWcuCiogICBAcmV0dXJuIHN0cmluZyBUaGUgcmVzdWx0aW5nIEhUTUwgY29kZSwgd2hpY2ggdGhlIDxlOmZ1bmN0aW9uPiB0YWcgd2l0aGluIHRoZSB0ZW1wbGF0ZSBpcyByZXBsYWNlZCB3aXRoLgoqLwoKcHVibGljIGZ1bmN0aW9uIHByb2Nlc3Moc3RyaW5nICRodG1sLCBhcnJheSAkZGF0YSA9IGFycmF5KCkpOnN0cmluZwp7CgoKICAgIC8vIFJldHVybgogICAgcmV0dXJuICRodG1sOwoKfQoKfQoK', 
        'lib' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlfjsKCnVzZSBhcGV4XERCOwp1c2UgYXBleFxyZWdpc3RyeTsKdXNlIGFwZXhcbG9nOwp1c2UgYXBleFxkZWJ1ZzsKCmNsYXNzIH5hbGlhc34KewoKCgp9Cgo=', 
        'modal' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflxtb2RhbDsKCnVzZSBhcGV4XERCOwp1c2UgYXBleFxyZWdpc3RyeTsKdXNlIGFwZXhcbG9nOwp1c2UgYXBleFxkZWJ1ZzsKCmNsYXNzIH5hbGlhc34gZXh0ZW5kcyBcYXBleFxhYnN0cmFjdHNcbW9kYWwKewoKLyoqCioqIFNob3cgdGhlIG1vZGFsIGJveC4gIFVzZWQgdG8gZ2F0aGVyIGFueSAKKiBuZWNlc3NhcnkgZGF0YWJhc2UgaW5mb3JtYXRpb24sIGFuZCBhc3NpZ24gdGVtcGxhdGUgdmFyaWFibGVzLCBldGMuCiovCgpwdWJsaWMgZnVuY3Rpb24gc2hvdygpCnsKCgp9Cgp9Cgo=', 
        'tabcontrol' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflx0YWJjb250cm9sOwoKdXNlIGFwZXhcREI7CnVzZSBhcGV4XHRlbXBsYXRlOwp1c2UgYXBleFxyZWdpc3RyeTsKdXNlIGFwZXhcbG9nOwp1c2UgYXBleFxkZWJ1ZzsKCmNsYXNzIH5hbGlhc34gZXh0ZW5kcyBcYXBleFxhYnN0cmFjdHNcdGFiY29udHJvbAp7CgogICAgLy8gRGVmaW5lIHRhYiBwYWdlcwogICAgcHVibGljICR0YWJwYWdlcyA9IGFycmF5KAogICAgICAgICdnZW5lcmFsJyA9PiAnR2VuZXJhbCBTZXR0aW5nc2UnIAogICAgKTsKCi8qKgoqIElzIGV4ZWN1dGVkIGV2ZXJ5IHRpbWUgdGhlIHRhYiBjb250cm9sIGlzIGRpc3BsYXllZCwgCiogaXMgdXNlZCB0byBwZXJmb3JtIGFueSBhY3Rpb25zIHN1Ym1pdHRlZCB3aXRoaW4gZm9ybXMgCiogb2YgdGhlIHRhYiBjb250cm9sLCBhbmQgbWFpbmx5IHRvIHJldHJpZXZlIGFuZCBhc3NpZ24gdmFyaWFibGVzIAoqIHRvIHRoZSB0ZW1wbGF0ZSBlbmdpbmUuCioKKiAgICAgQHBhcmFtIGFycmF5ICRkYXRhIFRoZSBhdHRyaWJ1dGVzIGNvbnRhaW5lZCB3aXRoaW4gdGhlIDxlOmZ1bmN0aW9uPiB0YWcgdGhhdCBjYWxsZWQgdGhlIHRhYiBjb250cm9sLgoqLwoKcHVibGljIGZ1bmN0aW9uIHByb2Nlc3MoYXJyYXkgJGRhdGEpIAp7CgoKfQoKfQoK', 
        'tabpage' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflx0YWJjb250cm9sXH5wYXJlbnR+OwoKdXNlIGFwZXhcREI7CnVzZSBhcGV4XHRlbXBsYXRlOwp1c2UgYXBleFxyZWdpc3RyeTsKdXNlIGFwZXhcbG9nOwp1c2UgYXBleFxkZWJ1ZzsKCmNsYXNzIH5hbGlhc34gZXh0ZW5kcyBcYXBleFxhYnN0cmFjdHNcdGFicGFnZQp7CgogICAgLy8gUGFnZSB2YXJpYWJsZXMKICAgIHB1YmxpYyAkcG9zaXRpb24gPSAnYm90dG9tJzsKICAgIHB1YmxpYyAkbmFtZSA9ICd+YWxpYXNfdWN+JzsKCi8qKgoqIEV4ZWN1dGVzIGV2ZXJ5IHRpbWUgdGhlIHRhYiBjb250cm9sIGlzIGRpc3BsYXllZCwgYW5kIHVzZWQgCiogdG8gZXhlY3V0ZSBhbnkgbmVjZXNzYXJ5IGFjdGlvbnMgZnJvbSBmb3JtcyBmaWxsZWQgb3V0IAoqIG9uIHRoZSB0YWIgcGFnZSwgYW5kIG1pYW5seSB0byB0cmVpZXZlIHZhcmlhYmxlcyBhbmQgYXNzaWduIAoqIHRoZW0gdG8gdGhlIHRlbXBsYXRlLgoqCiogICAgIEBwYXJhbSBhcnJheSAkZGF0YSBUaGUgYXR0cmlidXRlcyBjb250YWluZCB3aXRoaW4gdGhlIDxlOmZ1bmN0aW9uPiB0YWcgdGhhdCBjYWxsZWQgdGhlIHRhYiBjb250cm9sCiovCgpwdWJsaWMgZnVuY3Rpb24gcHJvY2VzcyhhcnJheSAkZGF0YSA9IGFycmF5KCkpIAp7CgoKfQoKfQoK', 
        'table' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflx0YWJsZTsKCnVzZSBhcGV4XERCOwp1c2UgYXBleFxyZWdpc3RyeTsKdXNlIGFwZXhcbG9nOwp1c2UgYXBleFxkZWJ1ZzsKCmNsYXNzIH5hbGlhc34gZXh0ZW5kcyBcYXBleFxhYnN0cmFjdHNcdGFibGUKewoKICAgIC8vIENvbHVtbnMKICAgIHB1YmxpYyAkY29sdW1ucyA9IGFycmF5KAogICAgICAgICdpZCcgPT4gJ0lEJwogICAgKTsKCiAgICAvLyBTb3J0YWJsZSBjb2x1bW5zCiAgICBwdWJsaWMgJHNvcnRhYmxlID0gYXJyYXkoJ2lkJyk7CgogICAgLy8gT3RoZXIgdmFyaWFibGVzCiAgICBwdWJsaWMgJHJvd3NfcGVyX3BhZ2UgPSAyNTsKICAgIHB1YmxpYyAkaGFzX3NlYXJjaCA9IGZhbHNlOwoKICAgIC8vIEZvcm0gZmllbGQgKGxlZnQtbW9zdCBjb2x1bW4pCiAgICBwdWJsaWMgJGZvcm1fZmllbGQgPSAnY2hlY2tib3gnOwogICAgcHVibGljICRmb3JtX25hbWUgPSAnfmFsaWFzfl9pZCc7CiAgICBwdWJsaWMgJGZvcm1fdmFsdWUgPSAnaWQnOyAKCiAgICAvLyBEZWxldGUgYnV0dG9uCiAgICBwdWJsaWMgJGRlbGV0ZV9idXR0b24gPSAnRGVsZXRlIENoZWNrZWQgfmFsaWFzX3VjfnMnOwogICAgcHVibGljICRkZWxldGVfZGJ0YWJsZSA9ICcnOwogICAgcHVibGljICRkZWxldGVfZGJjb2x1bW4gPSAnJzsKCi8qKgoqIFBhc3NlcyB0aGUgYXR0cmlidXRlcyBjb250YWluZWQgd2l0aGluIHRoZSA8ZTpmdW5jdGlvbj4gdGFnIHRoYXQgY2FsbGVkIHRoZSB0YWJsZS4KKiBVc2VkIG1haW5seSB0byBzaG93L2hpZGUgY29sdW1ucywgYW5kIHJldHJpZXZlIHN1YnNldHMgb2YgCiogZGF0YSAoZWcuIHNwZWNpZmljIHJlY29yZHMgZm9yIGEgdXNlciBJRCMpLgoqIAooICAgICBAcGFyYW0gYXJyYXkgJGRhdGEgVGhlIGF0dHJpYnV0ZXMgY29udGFpbmVkIHdpdGhpbiB0aGUgPGU6ZnVuY3Rpb24+IHRhZyB0aGF0IGNhbGxlZCB0aGUgdGFibGUuCiovCgpwdWJsaWMgZnVuY3Rpb24gZ2V0X2F0dHJpYnV0ZXMoYXJyYXkgJGRhdGEgPSBhcnJheSgpKQp7Cgp9CgovKioKKiBHZXQgdGhlIHRvdGFsIG51bWJlciBvZiByb3dzIGF2YWlsYWJsZSBmb3IgdGhpcyB0YWJsZS4KKiBUaGlzIGlzIHVzZWQgdG8gZGV0ZXJtaW5lIHBhZ2luYXRpb24gbGlua3MuCiogCiogICAgIEBwYXJhbSBzdHJpbmcgJHNlYXJjaF90ZXJtIE9ubHkgYXBwbGljYWJsZSBpZiB0aGUgQUpBWCBzZWFyY2ggYm94IGhhcyBiZWVuIHN1Ym1pdHRlZCwgYW5kIGlzIHRoZSB0ZXJtIGJlaW5nIHNlYXJjaGVkIGZvci4KKiAgICAgQHJldHVybiBpbnQgVGhlIHRvdGFsIG51bWJlciBvZiByb3dzIGF2YWlsYWJsZSBmb3IgdGhpcyB0YWJsZS4KKi8KcHVibGljIGZ1bmN0aW9uIGdldF90b3RhbChzdHJpbmcgJHNlYXJjaF90ZXJtID0gJycpOmludCAKewoKICAgIC8vIEdldCB0b3RhbAogICAgaWYgKCRzZWFyY2hfdGVybSAhPSAnJykgeyAKICAgICAgICAkdG90YWwgPSBEQjo6Z2V0X2ZpZWxkKCJTRUxFQ1QgY291bnQoKikgRlJPTSB+cGFja2FnZX5ffmFsaWFzfiBXSEVSRSBzb21lX2NvbHVtbiBMSUtFICVscyIsICRzZWFyY2hfdGVybSk7CiAgICB9IGVsc2UgeyAKICAgICAgICAkdG90YWwgPSBEQjo6Z2V0X2ZpZWxkKCJTRUxFQ1QgY291bnQoKikgRlJPTSB+cGFja2FnZX5ffmFsaWFzfiIpOwogICAgfQogICAgaWYgKCR0b3RhbCA9PSAnJykgeyAkdG90YWwgPSAwOyB9CgogICAgLy8gUmV0dXJuCiAgICByZXR1cm4gKGludCkgJHRvdGFsOwoKfQoKLyoqCiogR2V0cyB0aGUgYWN0dWFsIHJvd3MgdG8gZGlzcGxheSB0byB0aGUgd2ViIGJyb3dzZXIuCiogVXNlZCBmb3Igd2hlbiBpbml0aWFsbHkgZGlzcGxheWluZyB0aGUgdGFibGUsIHBsdXMgQUpBWCBiYXNlZCBzZWFyY2gsIAoqIHNvcnQsIGFuZCBwYWdpbmF0aW9uLgoqCiogICAgIEBwYXJhbSBpbnQgJHN0YXJ0IFRoZSBudW1iZXIgdG8gc3RhcnQgcmV0cmlldmluZyByb3dzIGF0LCB1c2VkIHdpdGhpbiB0aGUgTElNSVQgY2xhdXNlIG9mIHRoZSBTUUwgc3RhdGVtZW50LgoqICAgICBAcGFyYW0gc3RyaW5nICRzZWFyY2hfdGVybSBPbmx5IGFwcGxpY2FibGUgaWYgdGhlIEFKQVggYmFzZWQgc2VhcmNoIGJhc2UgaXMgc3VibWl0dGVkLCBhbmQgaXMgdGhlIHRlcm0gYmVpbmcgc2VhcmNoZWQgZm9ybS4KKiAgICAgQHBhcmFtIHN0cmluZyAkb3JkZXJfYnkgTXVzdCBoYXZlIGEgZGVmYXVsdCB2YWx1ZSwgYnV0IGNoYW5nZXMgd2hlbiB0aGUgc29ydCBhcnJvd3MgaW4gY29sdW1uIGhlYWRlcnMgYXJlIGNsaWNrZWQuICBVc2VkIHdpdGhpbiB0aGUgT1JERVIgQlkgY2xhdXNlIGluIHRoZSBTUUwgc3RhdGVtZW50LgoqICAgICBAcmV0dXJuIGFycmF5IEFuIGFycmF5IG9mIGFzc29jaWF0aXZlIGFycmF5cyBnaXZpbmcga2V5LXZhbHVlIHBhaXJzIG9mIHRoZSByb3dzIHRvIGRpc3BsYXkuCiovCnB1YmxpYyBmdW5jdGlvbiBnZXRfcm93cyhpbnQgJHN0YXJ0ID0gMCwgc3RyaW5nICRzZWFyY2hfdGVybSA9ICcnLCBzdHJpbmcgJG9yZGVyX2J5ID0gJ2lkIGFzYycpOmFycmF5IAp7CgogICAgLy8gR2V0IHJvd3MKICAgIGlmICgkc2VhcmNoX3Rlcm0gIT0gJycpIHsgCiAgICAgICAgJHJvd3MgPSBEQjo6cXVlcnkoIlNFTEVDVCAqIEZST00gfnBhY2thZ2V+X35hbGlhc34gV0hFUkUgc29tZV9jb2x1bW4gTElLRSAlbHMgT1JERVIgQlkgJG9yZGVyX2J5IExJTUlUICRzdGFydCwkdGhpcy0+cm93c19wZXJfcGFnZSIsICRzZWFyY2hfdGVybSk7CiAgICB9IGVsc2UgeyAKICAgICAgICAkcm93cyA9IERCOjpxdWVyeSgiU0VMRUNUICogRlJPTSB+cGFja2FnZX5ffmFsaWFzfiBPUkRFUiBCWSAkb3JkZXJfYnkgTElNSVQgJHN0YXJ0LCR0aGlzLT5yb3dzX3Blcl9wYWdlIik7CiAgICB9CgogICAgLy8gR28gdGhyb3VnaCByb3dzCiAgICAkcmVzdWx0cyA9IGFycmF5KCk7CiAgICBmb3JlYWNoICgkcm93cyBhcyAkcm93KSB7IAogICAgICAgIGFycmF5X3B1c2goJHJlc3VsdHMsICR0aGlzLT5mb3JtYXRfcm93KCRyb3cpKTsKICAgIH0KCiAgICAvLyBSZXR1cm4KICAgIHJldHVybiAkcmVzdWx0czsKCn0KCi8qKgoqIFJldHJpZXZlcyByYXcgZGF0YSBmcm9tIHRoZSBkYXRhYmFzZSwgd2hpY2ggbXVzdCBiZSAKKiBmb3JtYXR0ZWQgaW50byB1c2VyIHJlYWRhYmxlIGZvcm1hdCAoZWcuIGZvcm1hdCBhbW91bnRzLCBkYXRlcywgZXRjLikuCioKKiAgICAgQHBhcmFtIGFycmF5ICRyb3cgVGhlIHJvdyBmcm9tIHRoZSBkYXRhYmFzZS4KKiAgICAgQHJldHVybiBhcnJheSBUaGUgcmVzdWx0aW5nIGFycmF5IHRoYXQgc2hvdWxkIGJlIGRpc3BsYXllZCB0byB0aGUgYnJvd3Nlci4KKi8KcHVibGljIGZ1bmN0aW9uIGZvcm1hdF9yb3coYXJyYXkgJHJvdyk6YXJyYXkgCnsKCiAgICAvLyBGb3JtYXQgcm93CgoKICAgIC8vIFJldHVybgogICAgcmV0dXJuICRyb3c7Cgp9Cgp9Cgo=', 
        'test' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflx0ZXN0OwoKdXNlIGFwZXhcREI7CnVzZSBhcGV4XHJlZ2lzdHJ5Owp1c2UgYXBleFxsb2c7CnVzZSBhcGV4XGRlYnVnOwoKLyoqCiogQWRkIGFueSBuZWNlc3NhcnkgcGhwVW5pdCB0ZXN0IG1ldGhvZHMgaW50byB0aGlzIGNsYXNzLiAgWW91IG1heSBleGVjdXRlIGFsbCAKKiB0ZXN0cyBieSBydW5uaW5nOiAgcGhwIGFwZXgucGhwIHRlc3QgfnBhY2thZ2V+CiovCmNsYXNzIHRlc3RffmFsaWFzfiBleHRlbmRzIFxhcGV4XHRlc3QKewoKLyoqCiogc2V0VXAKKi8KcHVibGljIGZ1bmN0aW9uIHNldFVwKCk6dm9pZAp7Cgp9CgovKioKKiB0ZWFyRG93bgoqLwpwdWJsaWMgZnVuY3Rpb24gdGVhckRvd24oKTp2b2lkCnsKCn0KCgoKfQoK', 
        'worker' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4XH5wYWNrYWdlflx3b3JrZXI7Cgp1c2UgYXBleFxEQjsKdXNlIGFwZXhccmVnaXN0cnk7CnVzZSBhcGV4XGxvZzsKdXNlIGFwZXhcZGVidWc7CgpjbGFzcyB+YWxpYXN+CnsKCgoKfQoK'
    );


/**
* Create a new component.  Used via the apex.php script, 
* to create a new component including necessary files.
*
*     @param string $type The type of component (template, worker, lib, etc.)
*     @param string $comp_alias Alias of the component in Apex format (ie. PACKAGE:[PARENT:]ALIAS
*     @param string $owner Optional owner, only required for a few components (controller, tab_page, worker)
*/
public static function create(string $type, string $comp_alias, string $owner = '')
{

    // Perform necessary checks
    list($alias, $parent, $package, $value, $owner) = self::add_checks($type, $comp_alias, '', $owner);

    // Create template, if needed
    if ($type == 'template') { 
        return self::create_template($alias, $owner);
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
* Create a new template, including necessary files.  Used by 
* the apex.php script.
*/
protected static function create_template(string $uri, string $package)
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
    file_put_contents($php_file, base64_decode('PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCm5hbWVzcGFjZSBhcGV4OwoKCg=='));

    // Add component
    self::add('template', $uri, '', 0, $package);

    // Debug
    debug::add(4, fmsg("Successfully created new template at, {1}", $uri), __FILE__, __LINE__);

    // Return
    return array('template', $uri, $package, '');

}

/**
* Add a new component into the database.  This will not actually create the necessary 
* PHP / TPL files, and instead will only add the necessary row(s) into the database.
* 
*     @param string $type The type of component (htmlfunc, worker, hash, etc.)
*     @param string $comp_alias Alias of component in standard Apex format (PACKAGE:[PARENT:]ALIAS)
*     @param string $value Only required for a few components such as 'config', and is the value of the component
*     #param int $order_num If necessary (eg. tabpage) the order num of the component.
*      @param string $owner Only needed for controller and tabpage components, and is the owner package of the component.
*/
public static function add(string $type, string $comp_alias, string $value = '', int $order_num = 0, string $owner = '')
{

    // Perform necessary checks
    list($alias, $parent, $package, $value, $owner) = self::add_checks($type, $comp_alias, $value, $owner);

    // Update component, if needed
    if ($row = DB::get_row("SELECT * FROM internal_components WHERE type = %s AND package = %s AND parent = %s AND alias = %s", $type, $package, $parent, $alias)) { 

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
            DB::update('internal_components', $updates, "id = %i", $row['id']);
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
    DB::insert('internal_components', array(
        'order_num' => $order_num, 
        'type' => $type, 
        'owner' => $owner, 
        'package' => $package, 
        'parent' => $parent, 
        'alias' => $alias, 
        'value' => $value)
    );
    $component_id = DB::insert_id();

    // Add to redis
    registry::$redis->sadd('config:components', implode(":", array($type, $package, $parent, $alias)));

    // Add to redis -- components_packages
    $chk = $type . ':' . $alias;
    $value = registry::$redis->hexists('config:components_package', $chk) > 0 ? 2 : $package;
    registry::$redis->hset('config:components_package', $chk, $value);

}

/**
* Perform all necessary checks on a component before adding it to the database.
*     @param string $type The type of component (htmlfunc, worker, hash, etc.)
*     @param string $comp_alias Alias of component in standard Apex format (PACKAGE:[PARENT:]ALIAS)
*     @param string $value Only required for a few components such as 'config', and is the value of the component
*      @param string $owner Only needed for controller and tabpage components, and is the owner package of the component.
*/
protected static function add_checks(string $type, string $comp_alias, string $value = '', string $owner = '')
{

    // Split alias 
    $vars = explode(":", strtolower($comp_alias));
    if ($type != 'template' && count($vars) < 2 || count($vars) > 3) { 
        throw new ComponentException('invalid_comp_alias', $type, $comp_alias);
    }

    // Set component vars
    $package = $type == 'template' ? $owner : array_shift($vars);
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
*      @param string $package The package the cron job is being added to.
*     @param string $alias The alias of the crontab job.
*/
protected static function add_crontab(string $package, string $alias)
{

    // Load file
    if (!$cron = components::load('cron', $alias, $package)) { 
        throw new ComponentException('no_load', 'cron', '', $alias, $package);
    }

    // Get date
    $next_date = date::add_interval($cron->time_interval, time(), false);
    $name = isset($cron->name) ? $cron->name : $alias;

    // Add to database
    DB::insert('internal_crontab', array(
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
*     @param string $type The type of component being deleted (eg. 'config', 'htmlfunc', etc.).
*     @param string $comp_alias Alias of component to delete in standard Apex format (ie. PACKAGE:[PARENT:]ALIAS)
*     @return bool Whether or not the operation was successful.
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
    if (!$row = DB::get_row("SELECT * FROM internal_components WHERE package = %s AND type = %s AND parent = %s AND alias = %s", $package, $type, $parent, $alias)) { 
        return true;
    }

    // Delete files
    $files = components::get_all_files($type, $alias, $package, $parent);
    foreach ($files as $file) { 
        if (!file_exists(SITE_PATH . '/' . $file)) { continue; }
        @unlink(SITE_PATH . '/' . $file);
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
        $children = DB::query("SELECT * FROM internal_components WHERE type = %s AND package = %s AND parent = %s", $child_type, $package, $alias);
        foreach ($children as $crow) {
        $del_alias = $crow['package'] . ':' . $crow['parent'] . ':' . $crow['alias'];
            self::remove($crow['type'], $del_alias);
        }
    }

    // Delete from database
    DB::query("DELETE FROM internal_components WHERE package = %s AND type = %s AND parent = %s AND alias = %s", $package, $type, $parent, $alias);
    if ($type == 'cron') { 
        DB::query("DELETE FROM internal_crontab WHERE package = %s AND alias = %s", $package, $alias);
    }

    // Delete from redis
    registry::$redis->srem('config:components', implode(":", array($type, $package, $parent, $alias)));

    // Update redis components package as needed
    $chk = $type . ':' . $alias;
    if (registry::$redis->hget('config:components_package', $chk) != 2) { 
        registry::$redis->hdel('config:components_package', $chk);
    } else { 
        $chk_packages = DB::get_column("SELECT package FROM internal_components WHERE type = %s AND alias = %s AND parent = %s", $package, $alias, $parent);
        if (count($chk_packages) == 1) { 
            registry::$redis->hset('config:components_package', $chk, $chk_packages[0]);
        }
    }

    // Delete config / hash from redis
    if ($type == 'config') { 
        registry::$redis->hdel('config', $package . ':' . $alias);
    } elseif ($type == 'hash') { 
        registry::$redis->hdel('hash', $package . ':' . $alias);
    }

    // Debug / log
    debug::add(2, fmsg("Deleted component.  owner {1}, type: {2}, package: {3}, alias {4}, parent: {5}", $package, $type, $package, $alias, $parent), __FILE__, __LINE__);

    // Return
    return true;

}

}


