class FeedController {
    constructor($scope, $location, authManager) {
        if (!authManager.isAuthenticated()) {
            $location.path("/");
        }

        this.$scope = $scope;

        // Scope functions
        $scope.sanitize = () => this.sanitize();
    }

    $onInit() {
        // Variable definitions
        this.$scope.feedFilter = -1;
        this.$scope.feeds = [{ id: 1, name: "Xataka" }, { id: 2, name: "Genbeta" }];
        this.feedArticles = [{ id: 1, feed: 1, name: "Xataka", title: "Ejemplo de feed", "description": "Ejemplo de descripción con <b>html incrustado</b>", updated: new Date() }];
        this.$scope.feedsFiltered = this.feedArticles;

        let self = this;
        this.$scope.$watch("feedFilter", (newValue, oldValue) => {
            if (newValue === oldValue) { return; }
            self.filterFeeds(newValue);
        });
    }

    filterFeeds(id) {
        let feedId = Number(id);

        if (feedId !== -1) {
            let tmpFiltered = [];
            for (let ind in this.feedArticles) {
                if (this.feedArticles[ind].feed === feedId) {
                    tmpFiltered.push(this.feedArticles[ind]);
                }
            }

            this.$scope.feedsFiltered = tmpFiltered;
        }
        else {
            this.$scope.feedsFiltered = this.feedArticles;
        }
    }

    sanitize() { }
}

export { FeedController };