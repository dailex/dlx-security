function (commit) {
    if (/^dlx\.security\.user\-/.test(commit._id) && commit.streamRevision) {
        emit(commit.iso_date, 1);
    }
}