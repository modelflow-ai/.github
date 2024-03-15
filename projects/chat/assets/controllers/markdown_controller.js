import { Controller } from '@hotwired/stimulus';
import { Converter } from 'showdown';

export default class extends Controller {
    static targets = ['markdown', 'container'];

    connect() {
        const classMap = {
            h1: 'text-4xl font-bold mb-4',
            h2: 'text-3xl font-bold mb-3',
            h3: 'text-2xl font-bold mb-2',
            ul: 'list-disc list-inside',
            li: 'mb-2',
            p: 'mb-4',
            blockquote: 'border-l-4 border-gray-900 pl-4 italic',
            code: 'bg-gray-100 text-red-500 p-2 rounded-lg shadow',
            a: 'text-blue-500 hover:text-blue-800'
        }

        const bindings = Object.keys(classMap).map(key => ({
            type: 'output',
            regex: new RegExp(`<${key}(.*)>`, 'g'),
            replace: `<${key} class="${classMap[key]}" $1>`
        }));

        var converter = new Converter({
            extensions: [...bindings]
        });

        this.containerTarget.classList.add('markdown-body');
        this.containerTarget.innerHTML = converter.makeHtml(this.markdownTarget.innerText);

        const observer = new MutationObserver(() => {
            this.containerTarget.innerHTML = converter.makeHtml(this.markdownTarget.innerText);
        });
        observer.observe(this.markdownTarget, {childList: true});
    }
}
