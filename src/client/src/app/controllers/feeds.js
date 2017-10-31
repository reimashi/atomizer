class FeedController {
    constructor($scope, $location, $timeout, authManager, apiService) {
        if (!authManager.isAuthenticated()) {
            $location.path("/");
        }

        this.$scope = $scope;
        this.$timeout = $timeout;
        this.api = apiService;

        // Scope functions
        $scope.addFeed = () => this.addFeed();
    }

    $onInit() {
        // Variable definitions
        this.$scope.newFeedUrl = "";
        this.$scope.feedFilter = -1;
        this.$scope.feeds = [];
        this.feedArticles = [];
        this.$scope.feedsFiltered = [];

        this.updateFeeds().then(() => { this.updateFeeds(true); });

        let self = this;
        this.$scope.$watch("feedFilter", (newValue, oldValue) => {
            if (newValue === oldValue) { return; }
            self.filterFeeds(newValue);
        });
    }

    updateFeeds(update) {
        let self = this;
        return new Promise((accept, reject) => {
            this.api.getFeeds(Boolean(update))
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

                    self.$timeout(function() {
                        self.$scope.$apply(() => {
                            self.$scope.feeds = tmpFeeds;
                            self.feedArticles = tmpArticles;
                            self.filterFeeds(self.$scope.feedFilter);
                        });
                    });

                    accept();
                })
                .catch((err) => { console.error(err); reject(err); });
        });
    }

    filterFeeds(id) {
        let self = this;
        let feedId = Number(id);

        if (feedId !== -1) {
            let tmpFiltered = [];
            for (let ind in self.feedArticles) {
                if (self.feedArticles[ind].feed === feedId) {
                    tmpFiltered.push(self.feedArticles[ind]);
                }
            }

            self.$timeout(function() {
                self.$scope.$apply(() => {
                    self.$scope.feedsFiltered = tmpFiltered;
                });
            });
        }
        else {
            self.$timeout(function() {
                self.$scope.$apply(() => {
                    self.$scope.feedsFiltered = self.feedArticles;
                });
            });
        }
    }

    addFeed() {
        if (this.$scope.newFeedUrl && this.$scope.newFeedUrl.length > 0) {
            let url = String(this.$scope.newFeedUrl);

            this.api.addFeed(url)
                .then(() => {
                    this.updateFeeds(true);
                })
                .catch((err) => {
                    console.error(err);
                })
        }
    }
}

export { FeedController };