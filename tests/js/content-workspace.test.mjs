import test from 'node:test';
import assert from 'node:assert/strict';
import {acceptSnapshot, acceptPoll, canReplaceForm, reconcileSourceChoices} from '../../public/js/content-workspace.js';

test('a poll started before a claim or release cannot revoke the current lease', () => {
    assert.equal(acceptPoll(3,4,0,0),false);
    assert.equal(acceptPoll(4,4,0,1),false);
    assert.equal(acceptPoll(4,4,1,1),true);
});

test('a delayed response cannot restore an obsolete revision', () => {
    assert.equal(acceptSnapshot({revision:8},{revision:7}),false);
    assert.equal(acceptSnapshot({revision:8},{revision:9}),true);
});
test('incoming data never replaces unsaved typing, including an expired editing session', () => {
    for (const editing of [true,false]) assert.equal(canReplaceForm({dirty:true,editing,incomingRevision:9,currentRevision:8}),false);
});
test('a read-only observer can receive a new revision while an active editor must reconcile it', () => {
    assert.equal(canReplaceForm({dirty:false,editing:false,incomingRevision:9,currentRevision:8}),true);
    assert.equal(canReplaceForm({dirty:false,editing:true,incomingRevision:9,currentRevision:8}),false);
});

test('source selections survive pagination, but changed and locked sources are dropped', () => {
    const choices = new Map([[1,{revision:2,text:true,images:[]}],[2,{revision:3,text:false,images:[10]}],[3,{revision:4,text:true,images:[]}],[4,{revision:5,text:true,images:[]}]]);
    const next = reconcileSourceChoices(choices,[{id:1,revision:2,editor:null},{id:2,revision:4,editor:null},{id:3,revision:4,editor:'Admin RW'}]);
    assert.deepEqual([...next.keys()],[1,4]);
    assert.deepEqual(next.get(4),choices.get(4));
    assert.equal(choices.size,4);
});
