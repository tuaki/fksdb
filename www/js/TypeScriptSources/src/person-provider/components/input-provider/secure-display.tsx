import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { WrappedFieldProps } from 'redux-form';
import { IPersonSelector } from '../../../brawl-registration/middleware/price';
import Lang from '../../../lang/components/lang';
import { clearProviderProviderProperty } from '../../actions';
import { IStore } from '../../interfaces';

interface IProps {
    personSelector: IPersonSelector;
    JSXLabel: JSX.Element;
}

interface IState {
    removeProviderValue?: () => void;
}

class SecureDisplay extends React.Component<WrappedFieldProps & IProps & IState, {}> {

    public render() {

        const {removeProviderValue, JSXLabel} = this.props;

        return <div className="form-group">
            <label className="text-success">{JSXLabel}<span className="fa fa-check ml-1"/></label>
            <small className="text-muted form-text">
                <Lang text={'Tento udaj už v systéme máme uložený, ak ho chcete zmeniť kliknite na tlačítko upraviť'}/>
            </small>
            <button className="btn btn-warning btn-sm" onClick={(event) => {
                event.preventDefault();
                removeProviderValue();
            }}>
                <span className="fa fa-edit mr-1"/><Lang text={'Upraviť'}/>
            </button>
        </div>;

    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>, ownProps: WrappedFieldProps & IProps): IState => {
    const {personSelector: {accessKey}, input: {name}} = ownProps;
    return {
        removeProviderValue: () => dispatch(clearProviderProviderProperty(accessKey, name)),
    };
};

const mapStateToProps = (): IState => {
    return {};
};
export default connect(mapStateToProps, mapDispatchToProps)(SecureDisplay);
