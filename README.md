# Astro Starter Kit: Basics

```sh
npm create astro@latest -- --template basics
```

[![Open in StackBlitz](https://developer.stackblitz.com/img/open_in_stackblitz.svg)](https://stackblitz.com/github/withastro/astro/tree/latest/examples/basics)
[![Open with CodeSandbox](https://assets.codesandbox.io/github/button-edit-lime.svg)](https://codesandbox.io/p/sandbox/github/withastro/astro/tree/latest/examples/basics)
[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new/withastro/astro?devcontainer_path=.devcontainer/basics/devcontainer.json)

> 🧑‍🚀 **Seasoned astronaut?** Delete this file. Have fun!

![just-the-basics](https://github.com/withastro/astro/assets/2244813/a0a5533c-a856-4198-8470-2d67b1d7c554)

## 🚀 Project Structure

Inside of your Astro project, you'll see the following folders and files:

```text
/
├── public/
│   └── favicon.png
│   └── robots.txt
├── src/
│   ├── components/
│   │   └── ContactForm.astro
│   │   └── Footer.astro
│   │   └── LanguageSlector.astro
│   │   └── SelectLocation.astro
│   ├── layouts/
│   │   └── Layout.astro
│   ├── pages/
│   │    └── en/
│   │         └── index.astro
│   │     └── es/
│   │         └── index.astro
│   │     └── pt/
│   │         └── index.astro
│   ├── translations/
│   │    └── i18n.ts
│   │     └── ui.ts
│   │     └── utils.ts
│   ├── vendor/
│   │       └── vlucas/phpdotenv/
│   │       └── phpmailer/
│   └── server/
│            └── send-email.php
└── package.json
```

To learn more about the folder structure of an Astro project, refer to [our guide on project structure](https://docs.astro.build/en/basics/project-structure/).

## 🧞 Commands

All commands are run from the root of the project, from a terminal:

| Command                   | Action                                           |
| :------------------------ | :----------------------------------------------- |
| `npm install`             | Installs dependencies                            |
| `npm run dev`             | Starts local dev server at `localhost:4321`      |
| `npm run build`           | Build your production site to `./dist/`          |
| `npm run preview`         | Preview your build locally, before deploying     |
| `npm run astro ...`       | Run CLI commands like `astro add`, `astro check` |
| `npm run astro -- --help` | Get help using the Astro CLI                     |

## 👀 Want to learn more?

Feel free to check [our documentation](https://docs.astro.build) or jump into our [Discord server](https://astro.build/chat).


1. Se instala con composer.json usando el comando composer install. Esto crea la carpeta vendor con phpmailer y dotenv.
2. Se instala el repo de astro con npm install
3. Se configura segun empresa

Se usa para las dos empresas, LC y WC. 
Tiene archivo robots.txt y heades para no ser indexado.
Tiene 2 footers, uno por empresa con sus respectivas redes.

build:
    .send-email.php hay que cambiar el nombre de la empresa para la que se use y se sube.
    .archivo env se configura con las key correspondiente para cada sitio y se sube.