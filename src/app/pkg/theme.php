<?php
declare(strict_types = 1);

namespace apex\app\pkg;

use apex\app;
use apex\svc\db;
use apex\svc\debug;
use apex\app\sys\network;
use apex\svc\io;
use apex\app\exceptions\RepoException;
use apex\app\exceptions\ThemeException;
use CurlFile;


/**
 * Handles all theme functions including create, publish, download, install 
 * and remove. 
 */
class theme
{


    // Default files for new themes
    private $default_files = [
        'sections/header.tpl' => 'PCFET0NUWVBFIGh0bWw+CjxodG1sIGxhbmc9ImVuIj4KPGhlYWQ+CiAgICA8dGl0bGU+PGE6cGFnZV90aXRsZSB0ZXh0b25seT0iMSI+PC90aXRsZT4KCiAgICA8IS0tIEphdmFzY3JpcHQgLyBDU1MgZmlsZXMgaGVyZSAtLT4KCjwvaGVhZD4KCjxib2R5PgoKICAgIDxkaXYgY2xhc3M9IndyYXBwZXIiPgoKICAgICAgICA8aDE+PGE6cGFnZV90aXRsZSB0ZXh0b25seT0iMSI+PC9oMT4KCiAgICAgICAgPGE6Y2FsbG91dHM+CgoKCgo=', 
        'sections/footer.tpl' => 'CiAgICA8L2Rpdj4KCjwvYm9keT4KPC9odG1sPgoKCg==', 
        'layouts/default.tpl' => 'CjxhOnRoZW1lIHNlY3Rpb249ImhlYWRlci50cGwiPgoKPGE6cGFnZV9jb250ZW50cz4KCjxhOnRoZW1lIHNlY3Rpb249ImZvb3Rlci50cGwiPgoKCg==', 
        'theme.php' => 'PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCi8qKgogKiBUaGVtZSBjb25maWd1cmF0aW9uLiAgRGVmaW5lcyB2YXJpYWJsZSBiYXNpYyAKICogcHJvcGVydGllcyByZWdhcmRpbmcgdGhlIHRoZW1lLgoqLwpjbGFzcyB0aGVtZV9+YWxpYXN+IAp7CgogICAgLy8gUHJvcGVydGllcwogICAgcHVibGljICRhcmVhID0gJ35hcmVhfic7CiAgICBwdWJsaWMgJGFjY2VzcyA9ICdwdWJsaWMnOwogICAgcHVibGljICRuYW1lID0gJ35hbGlhc34nOwogICAgcHVibGljICRkZXNjcmlwdGlvbiA9ICcnOwoKICAgIC8vIEF1dGhvciBkZXRhaWxzCiAgICBwdWJsaWMgJGF1dGhvcl9uYW1lID0gJyc7CiAgICBwdWJsaWMgJGF1dGhvcl9lbWFpbCA9ICcnOwogICAgcHVibGljICRhdXRob3JfdXJsID0gJyc7CgogICAgLyoqCiAgICAgKiBFbnZhdG8gaXRlbSBJRC4gIGlmIHRoaXMgaXMgZGVmaW5lZCwgdXNlcnMgd2lsbCBuZWVkIHRvIHB1cmNoYXNlIHRoZSB0aGVtZSBmcm9tIFRoZW1lRm9yZXN0IGZpcnN0LCAKICAgICAqIGFuZCBlbnRlciB0aGVpciBsaWNlbnNlIGtleSBiZWZvcmUgZG93bmxvYWRpbmcgdGhlIHRoZW1lIHRvIEFwZXguICBUaGUgbGljZW5zZSBrZXkgCiAgICAgKiB3aWxsIGJlIHZlcmlmaWVkIHZpYSBFbnZhdG8ncyBBUEksIHRvIGVuc3VyZSB0aGUgdXNlciBwdXJjaGFzZWQgdGhlIHRoZW1lLgogICAgICogCiAgICAgKiBZb3UgbXVzdCBhbHNvIHNwZWNpZnkgeW91ciBFbnZhdG8gdXNlcm5hbWUsIGFuZCB0aGUgZnVsbCAKICAgICAqIFVSTCB0byB0aGUgdGhlbWUgb24gdGhlIFRoZW1lRm9yZXN0IG1hcmtldHBsYWNlLiAgUGxlYXNlIGFsc28gCiAgICAgKiBlbnN1cmUgeW91IGFscmVhZHkgaGF2ZSBhIGRlc2lnbmVyIGFjY291bnQgd2l0aCB1cywgYXMgd2UgZG8gbmVlZCB0byAKICAgICAqIHN0b3JlIHlvdXIgRW52YXRvIEFQSSBrZXkgaW4gb3VyIHN5c3RlbSBpbiBvcmRlciB0byB2ZXJpZnkgcHVyY2hhc2VzLiAgQ29udGFjdCB1cyB0byBvcGVuIGEgZnJlZSBhY2NvdW50LgogICAgICovCiAgICBwdWJsaWMgJGVudmF0b19pdGVtX2lkID0gJyc7CiAgICBwdWJsaWMgJGVudmF0b191c2VybmFtZSA9ICcnOwogICAgcHVibGljICRlbnZhdG9fdXJsID0gJyc7Cgp9Cgo=', 
        'tags.tpl' => 'CioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKgoqIFRoaXMgZmlsZSBjb250YWlucyBhbGwgSFRNTCBzbmlwcGV0cyBmb3IgdGhlIHNwZWNpYWwgCiogSFRNTCB0YWdzIHRoYXQgYXJlIHVzZWQgdGhyb3VnaG91dCBBcGV4LiAgVGhlc2UgYXJlIHRhZ3MgcHJlZml4ZWQgd2l0aCAiYToiLCBzdWNoIGFzIAoqIDxhOmJveD4sIDxhOmZvcm1fdGFibGU+LCBhbmQgb3RoZXJzLgoqCiogQmVsb3cgYXJlIGxpbmVzIHdpdGggdGhlIGZvcm1hdCAiW1t0YWdfbmFtZV1dIiwgYW5kIGV2ZXJ5dGhpbmcgYmVsb3cgdGhhdCAKKiBsaW5lIHJlcHJlc2VudHMgdGhlIGNvbnRlbnRzIG9mIHRoYXQgSFRNTCB0YWcsIHVudGlsIHRoZSBuZXh0IG9jY3VycmVuY2Ugb2YgIltbdGFnX25hbWVdXSIgaXMgcmVhY2hlZC4KKgoqIFRhZyBuYW1lcyB0aGF0IGNvbnRhaW4gYSBwZXJpb2QgKCIuIikgc2lnbmlmeSBhIGNoaWxkIGl0ZW0sIGFzIHlvdSB3aWxsIG5vdGljZSBiZWxvdy4KKgoqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioKCgoKKioqKioqKioqKioqKioqKioqKioKKiA8YTpmb3JtX3RhYmxlPiAuLi4gPC9hOmZvcm1fdGFibGU+CiogPGE6Rk9STV9GSUVMRD4KKgoqIFRoZSBmb3JtIHRhYmxlLCBhbmQgdmFyaW91cyBmb3JtIGZpZWxkIGVsZW1lbnRzCioqKioqKioqKioqKioqKioqKioqCgpbW2Zvcm1fdGFibGVdXQo8dGFibGUgYm9yZGVyPSIwIiBjbGFzcz0iZm9ybV90YWJsZSIgc3R5bGU9IndpZHRoOiB+d2lkdGh+OyBhbGlnbjogfmFsaWdufjsiPgogICAgfmNvbnRlbnRzfgo8L3RhYmxlPgoKCltbZm9ybV90YWJsZS5yb3ddXQo8dHI+CiAgICA8dGQ+PGxhYmVsIGZvcj0ifm5hbWV+Ij5+bGFiZWx+OjwvbGFiZWw+PC90ZD4KICAgIDx0ZD48ZGl2IGNsYXNzPSJmb3JtLWdyb3VwIj4KICAgICAgICB+Zm9ybV9maWVsZH4KICAgIDwvZGl2PjwvdGQ+CjwvdHI+CgoKW1tmb3JtX3RhYmxlLnNlcGFyYXRvcl1dCjx0cj4KICAgIDx0ZCBjb2xzcGFuPSIyIiBzdHlsZT0icGFkZGluZzogNXB4IDI1cHg7Ij48aDU+fmxhYmVsfjwvaDU+PC90ZD4KPC90cj4KCgpbW2Zvcm0uc3VibWl0XV0KPGJ1dHRvbiB0eXBlPSJzdWJtaXQiIG5hbWU9InN1Ym1pdCIgdmFsdWU9In52YWx1ZX4iIGNsYXNzPSJidG4gYnRuLXByaW1hcnkgYnRuLX5zaXplfiI+fmxhYmVsfjwvYnV0dG9uPgoKCltbZm9ybS5yZXNldF1dCjxidXR0b24gdHlwZT0icmVzZXQiIGNsYXNzPSJidG4gYnRuLXByaW1hcnkgYnRuLW1kIj5SZXNldCBGb3JtPC9idXR0b24+CgoKW1tmb3JtLmJ1dHRvbl1dCjxhIGhyZWY9In5ocmVmfiIgY2xhc3M9ImJ0biBidG4tcHJpbmFyeSBidG4tfnNpemV+Ij5+bGFiZWx+PC9hPgoKCltbZm9ybS5ib29sZWFuXV0KPGlucHV0IHR5cGU9InJhZGlvIiBuYW1lPSJ+bmFtZX4iIGNsYXNzPSJmb3JtLWNvbnRyb2wiIHZhbHVlPSIxIiB+Y2hrX3llc34+IFllcyAKPGlucHV0IHR5cGU9InJhZGlvIiBuYW1lPSJ+bmFtZX4iIGNsYXNzPSJmb3JtLWNvbnRyb2wiIHZhbHVlPSIwIiB+Y2hrX25vfj4gTm8gCgoKW1tmb3JtLnNlbGVjdF1dCjxzZWxlY3QgbmFtZT0ifm5hbWV+IiBjbGFzcz0iZm9ybS1jb250cm9sIiB+d2lkdGh+IH5vbmNoYW5nZX4+CiAgICB+b3B0aW9uc34KPC9zZWxlY3Q+CgoKW1tmb3JtLnRleHRib3hdXQo8aW5wdXQgdHlwZT0ifnR5cGV+IiBuYW1lPSJ+bmFtZX4iIHZhbHVlPSJ+dmFsdWV+IiBjbGFzcz0iZm9ybS1jb250cm9sIiBpZD0ifmlkfiIgfnBsYWNlaG9sZGVyfiB+YWN0aW9uc34gfnZhbGlkYXRpb25+IC8+CgoKW1tmb3JtLnRleHRhcmVhXV0KPHRleHRhcmVhIG5hbWU9In5uYW1lfiIgY2xhc3M9ImZvcm0tY29udHJvbCIgaWQ9In5pZH4iIHN0eWxlPSJ3aWR0aDogfndpZHRofjsgaGVpZ2h0OiB+aGVpZ2h0fjsiIH5wbGFjZWhvbGRlcn4+fnZhbHVlfjwvdGV4dGFyZWE+CgoKW1tmb3JtLnBob25lXV0KPHNlbGVjdCBuYW1lPSJ+bmFtZX5fY291bnRyeSIgY2xhc3M9ImZvcm0tY29udHJvbCIgc3R5bGU9IndpZHRoOiAzMHB4OyBmbG9hdDogbGVmdDsiPgogICAgfmNvdW50cnlfY29kZV9vcHRpb25zfgo8L3NlbGVjdD4gCjxpbnB1dCB0eXBlPSJ0ZXh0IiBuYW1lPSJ+bmFtZX4iIHZhbHVlPSJ+dmFsdWV+IiBjbGFzcz0iZm9ybS1jb250cm9sIiBzdHlsZT0id2lkdGg6IDE3MHB4OyBmbG9hdDogbGVmdDsiIH5wbGFjZWhvbGRlcn4+CgoKW1tmb3JtLmFtb3VudF1dCjxzcGFuIHN0eWxlPSJmbG9hdDogbGVmdDsiPn5jdXJyZW5jeV9zaWdufjwvc3Bhbj4gCjxpbnB1dCB0eXBlPSJ0ZXh0IiBuYW1lPSJ+bmFtZX4iIHZhbHVlPSJ+dmFsdWV+IiBjbGFzcz0iZm9ybS1jb250cm9sIiBzdHlsZT0id2lkdGg6IDYwcHg7IGZsb2F0OiBsZWZ0OyIgfnBsYWNlaG9sZGVyfiBkYXRhLXBhcnNsZXktdHlwZT0iZGVjaW1hbCI+CgoKW1tmb3JtLmRhdGVdXQo8c2VsZWN0IG5hbWU9In5uYW1lfl9tb250aCIgY2xhc3M9ImZvcm0tY29udHJvbCIgc3R5bGU9IndpZHRoOiAxMjBweDsgZmxvYXQ6IGxlZnQ7Ij4KICAgIH5tb250aF9vcHRpb25zfgo8L3NlbGVjdD4gCjxzZWxlY3QgbmFtZT0ifm5hbWV+X2RheX4iIGNsYXNzPSJmb3JtLWNvbnRyb2wiIHN0eWxlPSJ3aWR0aDogMzBweDsgZmxvYXQ6IGxlZnQ7Ij4KICAgIH5kYXlfb3B0aW9uc34KPC9zZWxlY3Q+LCAKPHNlbGVjdCBuYW1lPSJ+bmFtZX5feWVhcn4iIGNsYXNzPSJmb3JtLWNvbnRyb2wiIHN0eWxlPSJ3aWR0aDogNzBweDsgZmxvYXQ6IGxlZnQ7Ij4KICAgIH55ZWFyX29wdGlvbnN+Cjwvc2VsZWN0PgoKW1tmb3JtLnRpbWVdXQo8c2VsZWN0IG5hbWU9In5uYW1lfl9ob3VyIiBjbGFzcz0iZm9ybS1jb250cm9sIiBzdHlsZT0id2lkdGg6IDYwcHg7IGZsb2F0OiBsZWZ0OyI+CiAgICB+aG91cl9vcHRpb25zfgo8L3NlbGVjdD4gOiAKPHNlbGVjdCBuYW1lPSJ+bmFtZX5fbWluIiBjbGFzcz0iZm9ybS1jb250cm9sIiBzdHlsZT0id2lkdGg6IDYwcHg7IGZsb2F0OiBsZWZ0OyI+CiAgICB+bWludXRlX29wdGlvbnN+Cjwvc2VsZWN0PgoKCltbZm9ybS5kYXRlX2ludGVydmFsXV0KPGlucHV0IHR5cGU9InRleHQiIG5hbWU9In5uYW1lfl9udW0iIGNsYXNzPSJmb3JtLWNvbnRyb2wiIHZhbHVlPSJ+bnVtfiIgc3R5bGU9IndpZHRoOiAzMHB4OyBmbG9hdDogbGVmdDsiPiAKPHNlbGVjdCBuYW1lPSJ+bmFtZX5fcGVyaW9kIiBjbGFzcz0iZm9ybS1jb250cm9sIiBzdHlsZT0id2lkdGg6IDgwcHg7IGZsb2F0OiBsZWZ0OyI+CiAgICB+cGVyaW9kX29wdGlvbnN+Cjwvc2VsZWN0PgoKCgoKKioqKioqKioqKioqKioqKioqKioKKiA8YTpib3g+IC4uLiA8L2E6Ym94PgoqIDxhOmJveF9oZWFkZXIgdGl0bGU9Ii4uLiI+IC4uLiA8L2E6Ym94X2hlYWRlcj4KKgoqIENvbnRhaW5lcnMgLyBwYW5lbHMgdGhhdCBoZWxwIHNlcGFyYXRlIGRpZmZlcmVudCBzZWN0aW9ucyBvZiB0aGUgcGFnZS4gIENhbiBvcHRpb25hbGx5IAoqIGNvbnRhaW4gYSBoZWFkZXIgd2l0aCB0aXRsZS4KKioqKioqKioqKioqKioqKioqKioKCltbYm94XV0KPGRpdiBjbGFzcz0icGFuZWwgcGFuZWwtZGVmYXVsdCI+CiAgICB+Ym94X2hlYWRlcn4KICAgIDxkaXYgY2xhc3M9InBhbmVsLWJvZHkiPgogICAgICAgIH5jb250ZW50c34KICAgIDwvZGl2Pgo8L2Rpdj4KCltbYm94LmhlYWRlcl1dCjxzcGFuIHN0eWxlPSJib3JkZXItYm90dG9tOiAxcHggc29saWQgIzMzMzMzMzsgbWFyZ2luLWJvdHRvbTogOHB4OyI+CiAgICA8aDM+fnRpdGxlfjwvaDM+CiAgICB+Y29udGVudHN+Cjwvc3Bhbj4KCgoqKioqKioqKioqKioqKioqKioqKgoqIDxhOmlucHV0X2JveD4gLi4uIDwvYTppbnB1dF9ib3g+CioKKiBNZWFudCBmb3IgYSBmdWxsIHdpZHRoIHNpemVoLCBzaG9ydCBzaXplZCBiYXIuICBVc2VkIAoqIGZvciB0aGluZ3Mgc3VjaCBhcyBhIHNlYXJjaCB0ZXh0Ym94LCBvciBvdGhlciBiYXJzIHRvIHNlcGFyYXRlIHRoZW0gZnJvbSAKKiB0aGUgcmVzdCBvZiB0aGUgcGFnZSBjb250ZW50LgoqCiogRXhhbXBsZSBvZiB0aGlzIGlzIFVzZXJzLT5NYW5hZ2UgVXNlciBtZW51IG9mIHRoZSBhZG1pbmlzdHJhdGlvbiAKKiBwYW5lbCwgd2hlcmUgdGhlIHNlYXJjaCBib3ggaXMgc3Vycm91bmRlZCBieSBhbiBpbnB1dCBib3guCioqKioqKioqKioqKioqKioqKioqCgpbW2lucHV0X2JveF1dCjxkaXYgc3R5bGU9ImJhY2tncm91bmQ6ICNjZWNlY2U7IHdpZHRoOiA5NSU7IG1hcmdpbjogMTJweDsgcGFkZGluZzogMjBweDsgZm9udC1zaXplOiAxMnB0OyBjb2xvcjogI2VlZTsiPgogICAgfmNvbnRlbnRzfgo8L2Rpdj4KCgoKKioqKioqKioqKioqKioqKioqKioKKiA8YTpjYWxsb3V0cz4KKgoqIFRoZSBjYWxsb3V0cyAvIGluZm9ybWF0aW9uYWwgbWVzc2FnZXMgdGhhdCBhcmUgZGlzcGxheWVkIG9uIHRoZSAKKiB0b3Agb2YgcGFnZXMgYWZ0ZXIgYW4gYWN0aW9uIGlzIHBlcmZvcm1lZC4gIFRoZXNlIG1lc3NhZ2VzIGFyZSAKKiBmb3I6IHN1Y2Nlc3MsIGVycm9yLCB3YXJuaW5nLCBpbmZvLiAKKgoqIFRoZSBmaXJzdCBlbGVtZW50IGlzIHRoZSBIVE1MIGNvZGUgb2YgdGhlIGNhbGxvdXRzIGl0c2VsZiwgdGhlIHNlY29uZCAKKiBhbmQgdGhpcmQgZWxlbWVudHMgYXJlIEpTT04gZW5jb2RlZCBzdHJpbmdzIHRoYXQgZGVmaW5lIHRoZSAKKiBDU1MgYW5kIGljb24gYWxpYXNlcyB0byB1c2UgZm9yIGVhY2ggbWVzc2FnZSB0eXBlLgoqKioqKioqKioqKioqKioqKioqKgoKW1tjYWxsb3V0c11dCjxkaXYgY2xhc3M9ImNhbGxvdXQgY2FsbG91dC1+Y3NzX2FsaWFzfiB0ZXh0LWNlbnRlciI+PHA+CiAgICA8aSBjbGFzcz0iaWNvbiB+aWNvbn4iPjwvaT4gJzsKICAgIH5tZXNzYWdlc34KPC9wPjwvZGl2PgoKCltbY2FsbG91dHMuY3NzXV0KWwogICAgInN1Y2Nlc3MiOiAic3VjY2VzcyIsIAogICAgImVycm9yIjogImRhbmdlciIsIAogICAgIndhcm5pbmciOiAid2FybmluZyIsIAogICAgImluZm8iOiAiaW5mbyIKXQoKCltbY2FsbG91dHMuaWNvbl1dClsKICAgICJzdWNjZXNzIjogImZhIGZhLWNoZWNrIiwgCiAgICAiZXJyb3IiOiAiZmEgZmEtYmFuIiwgCiAgICAid2FybmluZyI6ICJmYSBmYS13YXJuaW5nIiwgCiAgICAiaW5mbyI6ICJmYSBmYS1pbmZvIgpdCgoKKioqKioqKioqKioqKioqKioqKioKKiA8YTpuYXZfbWVudT4KKgoqIFRoZSBuYXZpZ2F0aW9uIG1lbnUgb2YgdGhlIHRoZW1lLCBpbmNsdWRpbmcgaGVhZGVyIC8gc2VwYXJhdG9yIAoqIGl0ZW1zLCBwYXJlbnQgbWVudXMsIGFuZCBzdWJtZW51cy4KKioqKioqKioqKioqKioqKioqKioKCltbbmF2LmhlYWRlcl1dCjxsaSBjbGFzcz0ibmF2LWl0ZW0taGVhZGVyIj48ZGl2IGNsYXNzPSJ0ZXh0LXVwcGVyY2FzZSBmb250LXNpemUteHMgbGluZS1oZWlnaHQteHMiPn5uYW1lfjwvZGl2PiA8aSBjbGFzcz0iaWNvbi1tZW51IiB0aXRsZT0ifm5hbWV+Ij48L2k+PC9saT4KCgpbW25hdi5wYXJlbnRdXQo8bGkgY2xhc3M9Im5hdi1pdGVtIG5hdi1pdGVtLXN1Ym1lbnUiPgogICAgPGEgaHJlZj0ifnVybH4iIGNsYXNzPSJuYXYtbGluayI+fmljb25+IDxzcGFuPn5uYW1lfjwvc3Bhbj48L2E+CiAgICA8dWwgY2xhc3M9Im5hdiBuYXYtZ3JvdXAtc3ViIiBkYXRhLXN1Ym1lbnUtdGl0bGU9In5uYW1lfiI+CiAgICAgICAgfnN1Ym1lbnVzfgogICAgPC91bD4KPC9saT4KCgpbW25hdi5tZW51XV0KPGxpIGNsYXNzPSJuYXYtaXRlbSI+PGEgaHJlZj0ifnVybH4iIGNsYXNzPSJuYXYtbGluayI+fmljb25+fm5hbWV+PC9hPjwvbGk+CgoKKioqKioqKioqKioqKioqKioqKioKKiA8YTp0YWJfY29udHJvbD4gLi4uIDwvYTp0YWJfY29udHJvbD4KKiA8YTp0YWJfcGFnZSBuYW1lPSIuLi4iPiAuLi4gPC9hOnRhYl9wYWdlPgoqCiogVGhlIHRhYiBjb250cm9scy4gIEluY2x1ZGVzIHRoZSB0YWIgY29udHJvbCBpdHNlbGYsIG5hdiBpdGVtcyBhbmQgCiogdGhlIGJvZHkgcGFuZSBvZiB0YWIgcGFnZXMuCioqKioqKioqKioqKioqKioqKioqCgpbW3RhYl9jb250cm9sXV0KPGRpdiBjbGFzcz0ibmF2LXRhYnMtY3VzdG9tIj4KICAgIDx1bCBjbGFzcz0ibmF2IG5hdi10YWJzIj4KICAgICAgICB+bmF2X2l0ZW1zfgogICAgPC91bD4KCiAgICA8ZGl2IGNsYXNzPSJ0YWItY29udGVudCI+CiAgICAgICAgfnRhYl9wYWdlc34KICAgIDwvZGl2Pgo8L2Rpdj4nOwoKCltbdGFiX2NvbnRyb2wubmF2X2l0ZW1dXQo8bGkgY2xhc3M9In5hY3RpdmV+Ij48YSBocmVmPSIjdGFifnRhYl9udW1+IiBkYXRhLXRvZ2dsZT0idGFiIj5+bmFtZX48L2E+PC9saT4KCgpbW3RhYl9jb250cm9sLnBhZ2VdXQo8ZGl2IGNsYXNzPSJ0YWItcGFuZSB+YWN0aXZlfiIgaWQ9InRhYn50YWJfbnVtfiI+CiAgICB+Y29udGVudHN+CjwvZGl2Pic7CgoKW1t0YWJfY29udHJvbC5jc3NfYWN0aXZlXV0KYWN0aXZlCgoKCioqKioqKioqKioqKioqKioqKioqCiogPGE6ZGF0YV90YWJsZT4gLi4uIDwvYTpkYXRhX3RhYmxlPgoqIDxhOnRhYmxlX3NlYXJjaF9iYXI+CiogPGE6dGg+IC4uLiA8YTp0aD4KKiA8YTp0cj4gLi4uIDwvYTp0cj4KKgoqIFRoZSBkYXRhIHRhYmxlcyB1c2VkIHRocm91Z2hvdXQgdGhlIHNvZnR3YXJlLgoqKioqKioqKioqKioqKioqKioqKgoKW1tkYXRhX3RhYmxlXV0KPHRhYmxlIGNsYXNzPSJ0YWJsZSB0YWJsZS1ib3JkZXJlZCB0YWJsZS1zdHJpcGVkIHRhYmxlLWhvdmVyIiBpZD0ifnRhYmxlX2lkfiI+CiAgICA8dGhlYWQ+CiAgICB+c2VhcmNoX2Jhcn4KCiAgICA8dHI+CiAgICAgICAgfmhlYWRlcl9jZWxsc34KICAgIDwvdHI+CiAgICA8L3RoZWFkPgoKICAgIDx0Ym9keSBpZD0ifnRhYmxlX2lkfl90Ym9keSI+CiAgICAgICAgfnRhYmxlX2JvZHl+CiAgICA8L3Rib2R5PgoKICAgIDx0Zm9vdD48dHI+CiAgICAgICAgPHRkIGNvbHNwYW49In50b3RhbF9jb2x1bW5zfiIgYWxpZ249InJpZ2h0Ij4KICAgICAgICAgICAgfmRlbGV0ZV9idXR0b25+CiAgICAgICAgICAgIH5wYWdpbmF0aW9ufgogICAgICAgIDwvdGQ+CiAgICA8L3RyPjwvdGZvb3Q+CjwvdGFibGU+CgoKW1tkYXRhX3RhYmxlLnRoXV0KPHRoPn5zb3J0X2FzY34gfm5hbWV+IH5zb3J0X2Rlc2N+PC90aD4KCgpbW2RhdGFfdGFibGUuc29ydF9hc2NdXQo8YSBocmVmPSJqYXZhc2NyaXB0OmFqYXhfc2VuZCgnY29yZS9zb3J0X3RhYmxlJywgJ35hamF4X2RhdGF+JnNvcnRfY29sPX5jb2xfYWxpYXN+JnNvcnRfZGlyPWFzYycsICdub25lJyk7IiBib3JkZXI9IjAiIHRpdGxlPSJTb3J0IEFzY2VuZGluZyB+Y29sX2FsaWFzfiI+CiAgICA8aSBjbGFzcz0iZmEgZmEtc29ydC1hc2MiPjwvaT4KPC9hPgoKCltbZGF0YV90YWJsZS5zb3J0X2Rlc2NdXQo8YSBocmVmPSJqYXZhc2NyaXB0OmFqYXhfc2VuZCgnY29yZS9zb3J0X3RhYmxlJywgJ35hamF4X2RhdGF+JnNvcnRfY29sPX5jb2xfYWxpYXN+JnNvcnRfZGlyPWRlc2MnLCAnbm9uZScpOyIgYm9yZGVyPSIwIiB0aXRsZT0iU29ydCBEZWNlbmRpbmcgfmNvbF9hbGlhc34iPgogICAgPGkgY2xhc3M9ImZhIGZhLXNvcnQtZGVzYyI+PC9pPgo8L2E+CgoKW1tkYXRhX3RhYmxlLnNlYXJjaF9iYXJdXQo8dHI+CiAgICA8dGQgY29sc3Bhbj0ifnRvdGFsX2NvbHVtbnN+IiBhbGlnbj0icmlnaHQiPgogICAgICAgIDxpIGNsYXNzPSJmYSBmYS1zZWFyY2giPjwvaT4gCiAgICAgICAgPGlucHV0IHR5cGU9InRleHQiIG5hbWU9InNlYXJjaF9+dGFibGVfaWR+IiBwbGFjZWhvbGRlcj0ifnNlYXJjaF9sYWJlbH4uLi4iIGNsYXNzPSJmb3JtLWNvbnRyb2wiIHN0eWxlPSJ3aWR0aDogMjEwcHg7Ij4gCiAgICAgICAgPGEgaHJlZj0iamF2YXNjcmlwdDphamF4X3NlbmQoJ2NvcmUvc2VhcmNoX3RhYmxlJywgJ35hamF4X2RhdGF+JywgJ3NlYXJjaF9+dGFibGVfaWR+Jyk7IiBjbGFzcz0iYnRuIGJ0bi1wcmltYXJ5IGJ0bi1tZCI+fnNlYXJjaF9sYWJlbH48L2E+CiAgICA8L3RkPgo8L3RyPgoKCltbZGF0YV90YWJsZS5kZWxldGVfYnV0dG9uXV0KPGEgaHJlZj1cImphdmFzY3JpcHQ6YWpheF9jb25maXJtKCdBcmUgeW91IHN1cmUgeW91IHdhbnQgdG8gZGVsZXRlIHRoZSBjaGVja2VkIHJlY29yZHM/JywgJ2NvcmUvZGVsZXRlX3Jvd3MnLCAnfmFqYXhfZGF0YX4nLCAnfmZvcm1fbmFtZX4nKTsiIGNsYXNzPSJidG4gYnRuLXByaW1hcnkgYnRuLW1kIiBzdHlsZT0iZmxvYXQ6IGxlZnQ7Ij5+ZGVsZXRlX2J1dHRvbl9sYWJlbH48L2E+CgoKCioqKioqKioqKioqKioqKioqKioqCiogPGE6cGFnaW5hdGlvbj4KKgoqIFBhZ2luYXRpb24gbGlua3MsIGdlbmVyYWxseSBkaXNwbGF5ZWQgYXQgdGhlIGJvdHRvbSBvZiAKKiBkYXRhIHRhYmxlcywgYnV0IGNhbiBiZSB1c2VkIGFueXdoZXJlLgoqKioqKioqKioqKioqKioqKioqKgoKW1twYWdpbmF0aW9uXV0KPHNwYW4gaWQ9InBnbnN1bW1hcnlffmlkfiIgc3R5bGU9InZlcnRpY2FsLWFsaWduOiBtaWRkbGU7IGZvbnQtc2l6ZTogOHB0OyBtYXJnaW4tcmlnaHQ6IDdweDsiPgogICAgPGI+fnN0YXJ0X3JlY29yZH4gLSB+ZW5kX3JlY29yZH48L2I+IG9mIDxiPn50b3RhbF9yZWNvcmRzfjwvYj4KPC9zcGFuPgoKPHVsIGNsYXNzPSJwYWdpbmF0aW9uIiBpZCA9InBnbl9+aWR+Ij4KICAgIH5pdGVtc34KPC91bD4KCgpbW3BhZ2VpbmF0aW9uLml0ZW1dXQo8bGkgc3R5bGU9ImRpc3BsYXk6IH5kaXNwbGF5fjsiPjxhIGhyZWY9In51cmx+Ij5+bmFtZX48L2E+PC9saT4KCltbcGFnaW5hdGlvbi5hY3RpdmVfaXRlbV1dCjxsaSBjbGFzcz0iYWN0aXZlIj48YT5+cGFnZX48L2E+PC9saT4KCgoKKioqKioqKioqKioqKioqKioqKioKKiA8YTpkcm9wZG93bl9hbGVydHM+CiogPGE6ZHJvcGRvd25fbWVzc2FnZXM+CioKKiBUaGUgbGlzdCBpdGVtcyB1c2VkIGZvciB0aGUgdHdvIGRyb3AgZG93biBsaXN0cywgbm90aWZpY2F0aW9ucyAvIGFsZXJ0cyBhbmQgCiogbWVzc2FnZXMuICBUaGVzZSBhcmUgZ2VuZXJhbGx5IGRpc3BsYXllZCBpbiB0aGUgdG9wIHJpZ2h0IGNvcm5lciAKKiBvZiBhZG1pbiBwYW5lbCAvIG1lbWJlciBhcmVhIHRoZW1lcy4KKioqKioqKioqKioqKioqKioqKioKCgpbW2Ryb3Bkb3duLmFsZXJ0XV0KPGxpIGNsYXNzPSJtZWRpYSI+CiAgICA8ZGl2IGNsYXNzPSJtZWRpYS1ib2R5Ij4KICAgICAgICA8YSBocmVmPSJ+dXJsfiI+fm1lc3NhZ2V+CiAgICAgICAgPGRpdiBjbGFzcz0idGV4dC1tdXRlZCBmb250LXNpemUtc20iPn50aW1lfjwvZGl2PgogICAgPC9kaXY+CjwvbGk+CgoKW1tkcm9wZG93bi5tZXNzYWdlXV0KPGxpIGNsYXNzPSJtZWRpYSI+CiAgICA8ZGl2IGNsYXNzPSJtZWRpYS1ib2R5Ij4KCiAgICAgICAgPGRpdiBjbGFzcz0ibWVkaWEtdGl0bGUiPgogICAgICAgICAgICA8YSBocmVmPSJ+dXJsfiI+CiAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz0iZm9udC13ZWlnaHQtc2VtaWJvbGQiPn5mcm9tfjwvc3Bhbj4KICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPSJ0ZXh0LW11dGVkIGZsb2F0LXJpZ2h0IGZvbnQtc2l6ZS1zbSI+fnRpbWV+PC9zcGFuPgogICAgICAgICAgICA8L2E+CiAgICAgICAgPC9kaXY+CgogICAgICAgIDxzcGFuIGNsYXNzPSJ0ZXh0LW11dGVkIj5+bWVzc2FnZX48L3NwYW4+CiAgICA8L2Rpdj4KPC9saT4KCgoKKioqKioqKioqKioqKioqKioqKioKKiA8YTpib3hsaXN0cz4KKgoqIFRoZSBib3hsaXN0cyBhcyBzZWVuIG9uIHBhZ2VzIHN1Y2ggYXMgU2V0dGluZ3MtPlVzZXJzLiAgVXNlZCB0byAKKiBkaXNwbGF5IGxpbmtzIHRvIG11bHRpcGxlIHBhZ2VzIHdpdGggZGVzY3JpcHRpb25zLgoqKioqKioqKioqKioqKioqKioqKgoKW1tib3hsaXN0XV0KPHVsIGNsYXNzPSJib3hsaXN0Ij4KICAgIH5pdGVtc34KPC91bD4KCgoKW1tib3hsaXN0Lml0ZW1dXQo8bGk+PHA+PGEgaHJlZj0ifnVybH4iPgogICAgPGI+fnRpdGxlfjwvYj48YnIgLz4KICAgIH5kZXNjcmlwdGlvbn4KPC9wPjwvbGk+CgoKCg=='
    ];


/**
 * Create a new theme 
 *
 * @param string $theme_alias The alias of the new theme to create
 * @param int $repo_id The ID# of the repo to publish the theme to
 * @param string $area The area the theme is for ('members' or 'public'), defaults to 'public'
 *
 * @return int The ID# of the newly created theme
 */
public function create(string $theme_alias, int $repo_id, string $area = 'public')
{ 

    // Debug
    debug::add(2, tr("Starting create theme with alias, {1}", $theme_alias));

    // Initial check
    if ($theme_alias == '') { 
        throw new ThemeException('invalid_alias');
    } elseif (preg_match("/\s\W]/", $theme_alias)) { 
        throw new ThemeException('invalid_alias', $theme_alias);
    }
    $theme_alias = strtolower($theme_alias);

    // Check if theme exists
    if ($row = db::get_row("SELECT * FROM internal_themes WHERE alias = %s", $theme_alias)) { 
        throw new ThemeException('theme_exists', $theme_alias);
    }

    // Create directories
    $theme_dir = SITE_PATH . '/views/themes/' . $theme_alias;
    io::create_dir($theme_dir);
    io::create_dir("$theme_dir/sections");
    io::create_dir("$theme_dir/layouts");
    io::create_dir(SITE_PATH . '/public/themes/' . $theme_alias);

    // Save default files
    foreach ($this->default_files as $filename => $code) { 

        // Get code
        $code = base64_decode($code);
        $code = str_replace("~alias~", $theme_alias, $code);
        $code = str_replace("~area~", $area, $code);
        file_put_contents("$theme_dir/$filename", $code);
    }

    // Add to database
    db::insert('internal_themes', array(
        'is_owner' => 1,
        'repo_id' => $repo_id,
        'area' => $area,
        'alias' => $theme_alias,
        'name' => $theme_alias)
    );
    $theme_id = db::insert_id();

    // Debug
    debug::add(1, tr("Successfully created new theme with alias {1}", $theme_alias));

    // Return
    return $theme_id;

}

/**
 * Publish a theme to a repository 
 *
 * @param string $theme_alias The alias of the theme to publish
 */
public function publish(string $theme_alias)
{ 

    // Debug
    debug::add(3, tr("Starting to publish theme with alias, {1}", $theme_alias));

    // Get theme
    if (!$row = db::get_row("SELECT * FROM internal_themes WHERE alias = %s", $theme_alias)) { 
        throw new ThemeException('not_exists', $theme_alias);
    }

    // Load theme file
    $class_name = 'theme_' . $theme_alias;
    require_once(SITE_PATH . '/views/themes/' . $theme_alias . '/theme.php');
    $theme = new $class_name();

    // Debug
    debug::add(4, tr("Publishing theme, successfully loaded theme configuration for alias, {1}", $theme_alias));

    // Set variables
    $access = $theme->access ?? $row['access'];
    $area = $theme->area ?? $row['area'];
    $name = $theme->name ?? $row['name'];

    // Update database
    db::update('internal_themes', array(
        'area' => $area,
        'name' => $name),
    "alias = %s", $theme_alias);

    // Get repo
    if (!$repo_id = db::get_idrow('internal_repos', $row['repo_id'])) { 
        throw new RepoException('not_exists', $repo_id);
    }

    // Compile theme
    $zip_file = $this->compile($theme_alias);

    // Set request
    $request = array(
        'type' => 'theme',
        'version' => '1.0.0',
        'access' => $access,
        'area' => $area,
        'name' => $name,
        'description' => ($theme->description ?? ''),
        'author_name' => ($theme->author_name ?? ''),
        'author_email' => ($theme->author_email ?? ''),
        'author_url' => ($theme->author_url ?? ''),
        'envato_item_id' => ($theme->envato_item_id ?? ''),
        'envato_username' => ($theme->envato_username ?? ''),
        'envato_url' => ($theme->envato_url ?? ''),
        'contents' => new CurlFile($zip_file, 'application/gzip', $theme_alias . '.zip')
    );

    // Send repo request
    $client = app::make(network::class);
    $vars = $client->send_repo_request((int) $row['repo_id'], $theme_alias, 'publish', $request);

    // Debug
    debug::add(1, tr("Successfully published theme to repository, {1}", $theme_alias));


}

/**
 * Compile a theme into a zip archive 
 *
 * @param string $theme_alias The alias of the theme to archive
 */
protected function compile(string $theme_alias)
{ 

    // Debug
    debug::add(4, tr("Start compile theme with alias, {1}", $theme_alias));

// Create /public/ directory within theme
    $theme_dir = SITE_PATH . '/views/themes/' . $theme_alias;
    if (is_dir("$theme_dir/public")) { io::remove_dir("$theme_dir/public"); }
    io::create_dir("$theme_dir/public");

    // Copy over public directory
    $files = io::parse_dir(SITE_PATH . '/public/themes/' . $theme_alias);
    foreach ($files as $file) { 
        io::create_dir(dirname("$theme_dir/public/$file"));
    copy(SITE_PATH . '/public/themes/' . $theme_alias . '/' . $file, "$theme_dir/public/$file");
    }

    // Archive theme
    $zip_file = sys_get_temp_dir() . '/apex_theme_' . $theme_alias . '.zip';
    if (file_exists($zip_file)) { @unlink($zip_file); }
    io::create_zip_archive($theme_dir, $zip_file);

    // Clean up
    io::remove_dir("$theme_dir/public");

    // Debug
    debug::add(4, tr("Successfully compiled theme, {1}", $theme_alias));

    // Return
    return $zip_file;

}

/**
 * Download and install a theme 
 * 
 * @param string $theme_alias The alias of the theme to install. 
 * @param int $repo_id Optional ID# of the repo to download from.  If unspecified, all repos will be checked.
 */
public function install(string $theme_alias, int $repo_id = 0)
{ 

    // Debug
    debug::add(2, tr("Starting to download and install theme, {1}", $theme_alias));

    // Download
    list($repo_id, $zip_file, $vars) = $this->download($theme_alias, $repo_id);

    // Unpack zip archive
    $theme_dir = SITE_PATH . '/views/themes/' . $theme_alias;
    if (is_dir($theme_dir)) { io::remove_dir($theme_dir); }
    io::unpack_zip_archive($zip_file, $theme_dir);

    // Create /public/ directory
    $public_dir = SITE_PATH . '/public/themes/' . $theme_alias;
        if (is_dir($public_dir)) { io::remove_dir($public_dir); }
    io::create_dir($public_dir);

    // Copy over /public/ directory
    $files = io::parse_dir("$theme_dir/public");
    foreach ($files as $file) { 
        io::create_dir(dirname("$public_dir/$file"));
    copy("$theme_dir/public/$file", "$public_dir/$file");
    }
    io::remove_dir("$theme_dir/public");

    // Add to database
    db::insert('internal_themes', array(
        'is_owner' => 0,
        'repo_id' => $repo_id,
        'area' => $vars['area'],
        'alias' => $theme_alias,
        'name' => $vars['name'])
    );

    // Activate theme
    if ($vars['area'] == 'members') { 
        app::update_config_var('users:theme_members', $theme_alias);
    } else { 
        app::update_config_var('core:theme_public', $theme_alias);
    }

    // Debug
    debug::add(1, tr("Successfully downloaded and installed theme, {1}", $theme_alias));

    // Return
    return true;

}

/**
 * Download a theme from the repository. 
 * 
 * @param string $theme_alias The alias of the theme to download. 
 * @param int $repo_id Optional ID# of the repo to download from.  If unspecified, all repos will be checked.
 */
protected function download(string $theme_alias, int $repo_id = 0)
{ 

    // Debug
    debug::add(4, tr("Starting to download theme from repository, {1}", $theme_alias));

    // Initialize network client
    $network = app::make(network::class);

    // Get repo, if needed
    if ($repo_id == 0) { 

        // Check theme on all repos
        $repos = $network->check_package($theme_alias, 'theme');
        if (count($repos) == 0) { 
            throw new ThemeException('not_exists_repo', $theme_alias);
        }
        $repo_id = array_keys($repos)[0];
    }

    // Get repo
    if (!$repo = db::get_idrow('internal_repos', $repo_id)) { 
throw RepoException('not_exists', $repo_id);
    }

    // Download theme
    $vars = $network->send_repo_request((int) $repo_id, $theme_alias, 'download', array('type' => 'theme'));

    // Save zip file
    $zip_file = sys_get_temp_dir() . '/apex_theme_' . $theme_alias . '.zip';
    if (file_exists($zip_file)) { @unlink($zip_file); }
    file_put_contents($zip_file, base64_decode($vars['contents']));

    // Debug
    debug::add(4, tr("Successfully downloaded theme, {1}", $theme_alias));

    // Return
    return array($repo_id, $zip_file, $vars);

}

/**
 * Remove a theme from the system. 
 *
 * @param string $theme_alias The alias of the theme to remove.
 */
public function remove(string $theme_alias)
{ 

    // Debug
    debug::add(4, tr("Starting removal of theme, {1}", $theme_alias));

    // Ensure theme exists
    if (!$row = db::get_row("SELECT * FROM internal_themes WHERE alias = %s", $theme_alias)) { 
        throw new ThemeException('not_exists', $theme_alias);
    }

    // Remove dirs
    io::remove_dir(SITE_PATH . '/views/themes/' . $theme_alias);
    io::remove_dir(SITE_PATH . '/public/themes/' . $theme_alias);

    // Delete from database
    db::query("DELETE FROM internal_themes WHERE alias = %s", $theme_alias);

    // Debug
    debug::add(1, tr("Successfully deleted theme from system, {1}", $theme_alias));

    // Return
return true;

}


}

