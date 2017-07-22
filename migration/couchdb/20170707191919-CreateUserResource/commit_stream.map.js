function (commit) {
    if (/^dlx\.security\.user\-/.test(commit._id)) {
        emit([ commit.streamId, commit.streamRevision ], 1);
    }
}