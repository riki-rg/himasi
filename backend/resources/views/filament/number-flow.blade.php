<style>
    .fi-wi-stats-overview-stat .fi-stat-value {
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.01em;
    }

    /* Rolling digit ala NumberFlow */
    .nf-digit {
        display: inline-block;
        overflow: hidden;
        height: 1em;
        line-height: 1em;
        vertical-align: bottom;
    }
    .nf-digit__col {
        display: flex;
        flex-direction: column;
        transition: transform 0.6s cubic-bezier(0.22, 0.61, 0.36, 1);
    }
    .nf-digit__cell {
        height: 1em;
        line-height: 1em;
        text-align: center;
    }

    @media (prefers-reduced-motion: reduce) {
        .nf-digit__col { transition: none; }
    }
</style>

<script>
    (function () {
        var DIGITS = '0123456789';

        function buildDigit(ch) {
            if (!/\d/.test(ch)) {
                var s = document.createElement('span');
                s.textContent = ch;
                return s;
            }
            var wrap = document.createElement('span');
            wrap.className = 'nf-digit';
            var col = document.createElement('span');
            col.className = 'nf-digit__col';
            for (var i = 0; i < 10; i++) {
                var cell = document.createElement('span');
                cell.className = 'nf-digit__cell';
                cell.textContent = String(i);
                col.appendChild(cell);
            }
            wrap.appendChild(col);
            requestAnimationFrame(function () {
                col.style.transform = 'translateY(-' + Number(ch) + '00%)';
            });
            return wrap;
        }

        function render(el) {
            if (el.dataset.nf === '1') return;
            el.dataset.nf = '1';
            var text = el.textContent.trim();
            var firstDigit = text.search(/\d/);
            if (firstDigit === -1) return;
            var lastDigit = text.lastIndexOf(text.match(/\d(?=(\D*)?$)/)[0]);
            var prefix = text.slice(0, firstDigit);
            var body = text.slice(firstDigit, lastDigit + 1);
            var suffix = text.slice(lastDigit + 1);

            el.textContent = '';
            prefix.split('').forEach(function (c) { el.appendChild(buildDigit(c)); });
            body.split('').forEach(function (c) { el.appendChild(buildDigit(c)); });
            suffix.split('').forEach(function (c) { el.appendChild(buildDigit(c)); });
        }

        function enhance(root) {
            root.querySelectorAll('.fi-stat-value:not([data-nf])').forEach(render);
        }

        document.addEventListener('DOMContentLoaded', function () { enhance(document); });

        var observer = new MutationObserver(function (muts) {
            muts.forEach(function (m) {
                m.addedNodes.forEach(function (n) {
                    if (n.nodeType !== 1) return;
                    if (n.matches && n.matches('.fi-stat-value')) render(n);
                    else enhance(n);
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    })();
</script>
