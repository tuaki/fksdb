import { combineReducers } from 'redux';
import {
    IState,
    submit,
} from '../../../fetch-api/reducers/submit';
import {
    fyziklaniData,
    IFyziklaniDataState,
} from '../../helpers/reducers/data';
import {
    fyziklaniDownloader,
    IFyziklaniDownloaderState,
} from '../../helpers/reducers/downloader';
import {
    fyziklaniOptions,
    IFyziklaniOptionsState,
} from '../../helpers/reducers/options';
import {
    fyziklaniTimer,
    IFyziklaniTimerState,
} from '../../helpers/reducers/timer';
import {
    fyziklaniTableFilter,
    IFyziklaniTableFilterState,
} from './table-filter';

export const app = combineReducers({
    data: fyziklaniData,
    downloader: fyziklaniDownloader,
    fetchApi: submit,
    options: fyziklaniOptions,
    tableFilter: fyziklaniTableFilter,
    timer: fyziklaniTimer,
});

export interface IFyziklaniResultsStore {
    data: IFyziklaniDataState;
    tableFilter: IFyziklaniTableFilterState;
    timer: IFyziklaniTimerState;
    options: IFyziklaniOptionsState;
    downloader: IFyziklaniDownloaderState;
    fetchApi: IState;
}
