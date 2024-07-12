const kaomojis = ["ヽ(´ー｀)ノ", "(;´Д`)", "ヽ(´∇`)ノ", "(´人｀)", "(＾Д^)", "(´ー`)", "（ ´,_ゝ`）", "(´～`)",
    "(;ﾟДﾟ)", "(;ﾟ∀ﾟ)", "┐(ﾟ～ﾟ)┌", "ヽ(`Д´)ノ", "( ´ω`)", "(ﾟー｀)", "(・∀・)", "（⌒∇⌒ゞ）", "(ﾟ血ﾟ#)", "(ﾟｰﾟ)",
    "(´￢`)", "(´π｀)", "ヽ(ﾟρﾟ)ノ", "Σ(;ﾟДﾟ)", "Σ(ﾟдﾟ|||)", "ｷﾀ━━━(・∀・)━━━!!"];

if (kaomojis.length > 0) {
    const formThread = document.getElementById('formThread');
    const details = document.createElement('details');
    const summary = document.createElement('summary');
    summary.textContent = "Kaomoji";
    details.appendChild(summary);

    const buttonContainer = document.createElement('div');
    buttonContainer.classList.add('kaomoji-buttons');

    kaomojis.forEach(kaomoji => {
        const button = document.createElement('button');
        button.textContent = kaomoji;
        button.classList.add('kaomoji-button');
        button.type = 'button';
        button.addEventListener('click', () => {
            const textarea = document.getElementById('comment');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const before = text.substring(0, start);
            const after = text.substring(end, text.length);
            textarea.value = before + kaomoji + after;
            textarea.selectionStart = textarea.selectionEnd = start + kaomoji.length;
            textarea.focus();
        });
        buttonContainer.appendChild(button);
    });

    details.appendChild(buttonContainer);
    formThread.appendChild(details);
}