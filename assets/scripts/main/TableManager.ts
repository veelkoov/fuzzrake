export default class TableManager {
    public constructor(
        private readonly $body: JQuery,
    ) {
    }

    public updateWith(data): void { // TODO: Typehint
        this.$body.empty();

        // TODO: Recreate original structure below (name cell etc.)
        data.forEach((value, index) => this.$body.append(`<tr data-index="${index}" class="artisan-data"><td class="name" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">${value[2]}</td><td class="maker-id" data-bs-toggle="modal" data-bs-target="#artisanDetailsModal">${value[0]}</td></tr>`));
    }
}
