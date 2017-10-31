class FeedController {
    constructor($scope, $location, authManager, apiService, toastr) {
        if (!authManager.isAuthenticated()) {
            $location.path("/");
        }

        this.$scope = $scope;
        this.api = apiService;
        this.toastr = toastr;
        toastr.clear([toast]);

        // Scope functions
        $scope.addFeed = () => this.addFeed();
    }

    $onInit() {
        // Variable definitions
        this.$scope.newFeedUrl = "";
        this.$scope.feedFilter = -1;
        this.$scope.feeds = [{ id: 1, name: "Xataka" }, { id: 2, name: "Genbeta" }];
        this.feedArticles = [{ id: 1, feed: 1, name: "Xataka", title: "Ejemplo de feed", "description": "Ejemplo de descripción con <b>html incrustado</b>", updated: new Date() }];
        this.$scope.feedsFiltered = this.feedArticles;

        let self = this;
        this.updateFeeds();

        this.$scope.$watch("feedFilter", (newValue, oldValue) => {
            if (newValue === oldValue) { return; }
            self.filterFeeds(newValue);
        });
    }

    updateFeeds() {
        let self = this;
        this.api.getFeeds()
            .then((data) => {
                let tmpFeeds = [];

                for (let fid in data) {
                    tmpFeeds.push({
                        id: data[fid].id,
                        name: data[fid].title,
                    });
                }

                let tmpArticles = [];

                for (let fid in data) {
                    for (let atid in data[fid].items) {
                        tmpArticles.push({
                            id: data[fid].items[atid].id,
                            feed: data[fid].id,
                            name: data[fid].title,
                            title: data[fid].items[atid].title,
                            description: data[fid].items[atid].summary,
                            updated: data[fid].items[atid].updated
                        });
                    }
                }

                self.$scope.feeds = tmpFeeds;
                self.feedArticles = tmpArticles;
                self.filterFeeds(self.$scope.feedFilter);
            })
            .catch((err) => { console.error(err); });
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

    addFeed() {
        if (this.$scope.newFeedUrl && this.$scope.newFeedUrl.length > 0) {
            let url = String(this.$scope.newFeedUrl);

            this.api.addFeed(url)
                .then(() => {
                    // Reload
                })
                .catch((err) => {
                    console.error(err);
                })
        }
    }
}

export { FeedController };