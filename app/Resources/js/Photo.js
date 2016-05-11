module.exports = function() {
    this.nom = null;
    this.uploaded = false;
    this.height = 0;
    this.width = 0;
    this.file = null;
    this.thumbnailStatus = {
        total: 0,
        loaded: 0
    };
    this.fullStatus = {
        total: 0,
        loaded: 0
    };
};