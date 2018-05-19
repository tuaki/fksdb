import { combineReducers } from 'redux';

import { reducer as formReducer } from 'redux-form';
import {
    IState as ISubmitStore,
    submit,
} from '../../shared/reducers/submit';
import {
    definitions,
    IDefinitionsState,
} from './definitions';
import {
    IProviderStore,
    provider,
} from '../../person-provider/reducers/provider';

export const app = combineReducers({
    definitions,
    form: formReducer,
    provider,
    submit,
});

export interface IStore {
    definitions: IDefinitionsState;
    form: typeof formReducer;
    submit: ISubmitStore;
    provider: IProviderStore;
}
