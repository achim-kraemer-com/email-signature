async function copySignature(signatureId) {
    console.log('läuft:', signatureId);

    const signatureEl = document.getElementById(signatureId);
    if (!signatureEl) {
        console.error('Signatur nicht gefunden:', signatureId);
        return;
    }

    const signature = signatureEl.innerHTML;

    try {
        const blob = new Blob([signature], { type: "text/html" });
        const data = [new ClipboardItem({ "text/html": blob })];
        await navigator.clipboard.write(data);

        alert("Signatur kopiert!");
    } catch (err) {
        console.error("Kopieren fehlgeschlagen:", err);
        alert("Kopieren fehlgeschlagen. Versuche es manuell.");
    }
}

document
    .getElementById('copy-signature-1')
    .addEventListener('click', () => copySignature('signature1'));

document
    .getElementById('copy-signature-2')
    .addEventListener('click', () => copySignature('signature2'));
