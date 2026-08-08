/**
 * Shared report PDF download helper.
 * Buttons: <button class="js-download-report-pdf" data-pdf-url="..." data-pdf-name="Report.pdf">
 */
(function (window, document) {
    'use strict';

    function withDownloadParam(url) {
        if (!url) {
            return url;
        }
        var sep = url.indexOf('?') === -1 ? '?' : '&';
        return url + sep + 'download=1&_=' + Date.now();
    }

    function suggestName(url, explicit) {
        if (explicit) {
            return /\.pdf$/i.test(explicit) ? explicit : (explicit + '.pdf');
        }
        try {
            var path = String(url || '').split('?')[0];
            var last = path.split('/').pop() || 'report';
            last = decodeURIComponent(last).replace(/[^\w.\-]+/g, '_');
            return /\.pdf$/i.test(last) ? last : (last + '.pdf');
        } catch (e) {
            return 'report.pdf';
        }
    }

    function triggerBlobDownload(blob, filename) {
        var pdfBlob = blob;
        if (!pdfBlob.type || pdfBlob.type.indexOf('pdf') === -1) {
            pdfBlob = new Blob([blob], { type: 'application/pdf' });
        }
        var objectUrl = URL.createObjectURL(pdfBlob);
        var a = document.createElement('a');
        a.href = objectUrl;
        a.download = filename;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        setTimeout(function () {
            try { URL.revokeObjectURL(objectUrl); } catch (e) {}
            if (a.parentNode) {
                a.parentNode.removeChild(a);
            }
        }, 1500);
    }

    window.downloadReportPdf = function (url, filename, buttonEl) {
        if (!url) {
            return;
        }
        var btn = buttonEl || null;
        var name = suggestName(url, filename || '');
        var downloadUrl = withDownloadParam(url);

        if (btn) {
            btn.disabled = true;
            if (!btn.getAttribute('data-original-html')) {
                btn.setAttribute('data-original-html', btn.innerHTML);
            }
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Downloading...';
        }

        function restore() {
            if (!btn) {
                return;
            }
            btn.disabled = false;
            var original = btn.getAttribute('data-original-html');
            if (original) {
                btn.innerHTML = original;
            }
        }

        if (window.fetch) {
            fetch(downloadUrl, {
                credentials: 'same-origin',
                cache: 'no-store'
            }).then(function (res) {
                if (!res.ok) {
                    throw new Error('HTTP ' + res.status);
                }
                var cd = res.headers.get('Content-Disposition') || '';
                var m = /filename\*?=(?:UTF-8''|")?([^\";]+)/i.exec(cd);
                if (m && m[1]) {
                    name = decodeURIComponent(m[1].replace(/"/g, '').trim());
                    if (!/\.pdf$/i.test(name)) {
                        name += '.pdf';
                    }
                }
                return res.blob();
            }).then(function (blob) {
                triggerBlobDownload(blob, name);
                restore();
            }).catch(function () {
                // Fallback: navigate via hidden iframe (server Content-Disposition: attachment)
                var iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = downloadUrl;
                document.body.appendChild(iframe);
                setTimeout(function () {
                    if (iframe.parentNode) {
                        iframe.parentNode.removeChild(iframe);
                    }
                    restore();
                }, 8000);
            });
        } else {
            window.location.href = downloadUrl;
            restore();
        }
    };

    function bind() {
        document.addEventListener('click', function (e) {
            var t = e.target;
            while (t && t !== document && !(t.classList && t.classList.contains('js-download-report-pdf'))) {
                t = t.parentNode;
            }
            if (!t || t === document) {
                return;
            }
            e.preventDefault();
            var url = t.getAttribute('data-pdf-url') || t.getAttribute('href');
            var name = t.getAttribute('data-pdf-name') || '';
            window.downloadReportPdf(url, name, t);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})(window, document);
