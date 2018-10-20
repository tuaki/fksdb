import * as React from 'react';
import { connect } from 'react-redux';
import { IFyziklaniResultsStore } from '../../../reducers';
import AutoSwitchControl from './auto-switch-control';
import MultiFilterControl from './multi-filter-control';

interface IState {
    autoSwitch?: boolean;
}

class Select extends React.Component<IState, {}> {

    public render() {
        const {autoSwitch} = this.props;

        return <div className="form-group">
            <button type="button" className="btn btn-primary" data-toggle="modal" data-target="#fyziklaniResultsOptionModal">
                <i className="fa fa-gear"/>
            </button>
            <div className="modal fade" id="fyziklaniResultsOptionModal" tabIndex={-1} role="dialog">
                <div className="modal-dialog" role="document">
                    <div className="modal-content">
                        <div className="modal-header">
                            <h5 className="modal-title">Options</h5>
                            <button type="button" className="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div className="modal-body">
                            <AutoSwitchControl/>
                            <hr/>
                            {autoSwitch ? (<MultiFilterControl/>) : (null)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
            ;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};
const mapStateToPros = (state: IFyziklaniResultsStore): IState => {
    return {
        autoSwitch: state.tableFilter.autoSwitch,
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(Select);